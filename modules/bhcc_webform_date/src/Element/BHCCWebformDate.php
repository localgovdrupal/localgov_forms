<?php

namespace Drupal\bhcc_webform_date\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\bhcc_webform_date\BHCCWebformHelper;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Utility\WebformArrayHelper;
use Drupal\Core\Datetime\DateHelper;

/**
 * Provides a 'bhcc_webform_date'.
 *
 * Webform composites contain a group of sub-elements.
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("bhcc_webform_date")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 */
class BHCCWebformDate extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $today = strtotime('now');
    $todayFormat = date('d/m/Y', $today);
    $description = 'For example ' . $todayFormat;
    $parentInfo = parent::getInfo();
    $childInfo = [
      '#title_display' => 'before',
    ];
    $returnInfo = array_replace($parentInfo, $childInfo);
    return $returnInfo + [
      '#theme' => 'bhcc_webform_date',
      '#description' => $description,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    // Generate a unique ID that can be used by #states.
    $html_id = Html::getUniqueId('bhcc_webform_date');

    $elements = [];
    $elements['day'] = [
      '#type' => 'number',
      '#title' => t('Day'),
      '#maxlength' => 2,
      '#min' => 1,
      '#max' => 31,
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--date',
        'placeholder' => t('DD'),
        'class' => ['date--day'],
      ],
    ];
    $elements['month'] = [
      '#type' => 'number',
      '#title' => t('Month'),
      '#maxlength' => 2,
      '#min' => 1,
      '#max' => 12,
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--month',
        'placeholder' => t('MM'),
        'class' => ['date--month'],
      ],
    ];
    $elements['year'] = [
      '#type' => 'number',
      '#title' => t('Year'),
      '#maxlength' => 4,
      '#min' => 1900,
      '#max' => 2100,
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--year',
        'placeholder' => t('YYYY'),
        'class' => ['date--year'],
      ],
    ];

    // Attach JS.
    $elements['#attached']['library'][] = 'bhcc_webform_date/bhcc_webform_date.date_element';

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateWebformComposite(&$element, FormStateInterface $form_state, &$form) {

    // Don't validate if its not visible.
    if (!Element::isVisibleElement($element) || !BHCCWebformHelper::isElementVisibleThroughParent($element, $form_state, $form)) {
      return;
    }

    // Unset any states required settings.
    // This is to stop individual fields generating an error.
    if (isset($element['day']['#states']['required']) || isset($element['month']['#states']['required']) || isset($element['year']['#states']['required'])) {
      $element['#required'] = TRUE;
      unset($element['day']['#states']['required']);
      unset($element['month']['#states']['required']);
      unset($element['year']['#states']['required']);
    }

    // Clear indivual form element errors,
    // as they can be set by this validation function.
    $element_key = $element['#webform_key'] ?? end($element['#parents']);
    $form_errors = $form_state->getErrors();
    $form_state->clearErrors();
    foreach($form_errors as $error_key => $error_value) {
      if (strpos($error_key, $element_key . ']') !== 0) {
        $form_state->setErrorByName($error_key, $error_value);
      }
    }

    // Get date values.
    $date_values = NestedArray::getValue($form_state->getValues(), $element['#parents']);

    // If the date elements is hidden on the form,
    // provide a default date element for validation.
    if (isset($element['#day__access'])) {
      $date_values['day'] = 1;
    }
    if (isset($element['#month__access'])) {
      $date_values['month'] = 1;
    }
    // Assume current year if year is hidden.
    if (isset($element['#year__access'])) {
      $date_values['year'] = date('Y');
    }

    // If all values are empty, check if the form is required.
    // Else skip validation to prevent stopping the form submitting.
    if (empty($date_values['day']) && empty($date_values['month']) && empty($date_values['year'])) {
      if (!empty($element['#required']) || (!empty($element['#day__required']) && !empty($element['#month__required']) && !empty($element['#year__required']))) {
        $form_state->setError($element, $element['#title'] . t(' field is required.'));
        return FALSE;
      } else {
        return;
      }
    }

    // Check the date is a valid date.
    $valid_date = checkdate((int) $date_values['month'], (int) $date_values['day'], (int) $date_values['year']);

    if (!$valid_date) {
      $form_state->setError($element, t('The date is not a correct date.'));
      return FALSE;
    }

    // Check that the year is in the valid range.
    if ((int) $date_values['year'] < 1900 || (int) $date_values['year'] > 2100) {
      $form_state->setError($element, t('The year must be between 1900 and 2100.'));
      return FALSE;
    }

    // Check date is in bounds (to min and max dates if set).
    $date_string = implode('-', $date_values);
    $date_to_time = strtotime($date_string);
    if (!empty($element['#date_date_min'])) {
      // Replace slashes with dashes so we always validate a UK format.
      $date_min_to_time = strtotime(str_replace('/', '-', $element['#date_date_min']));
      if ($date_to_time < $date_min_to_time) {
        $form_state->setError($element, t('The date must be on or after %date_min', [
          '%date_min' => date('d/m/Y', $date_min_to_time),
        ]));
        return FALSE;
      }
    }
    if (!empty($element['#date_date_max'])) {
      // Replace slashes with dashes so we always validate a UK format.
      $date_max_to_time = strtotime(str_replace('/', '-', $element['#date_date_max']));
      if ($date_to_time > $date_max_to_time) {
        $form_state->setError($element, t('The date must be on or before %date_max', [
          '%date_max' => date('d/m/Y', $date_max_to_time),
        ]));
        return FALSE;
      }
    }

    // Ensure that the input is a specified day of week - if any.
    if (!empty($element['#days_of_week'])) {
      $name = empty($element['#title']) ? $element['#parents'][0] : $element['#title'];
      $days = $element['#days_of_week'];
      $day = date('w', $date_to_time);
      if (!in_array($day, $days)) {
        $form_state->setError($element, t('%name must be a %days.', [
          '%name' => $name,
          '%days' => WebformArrayHelper::toString(array_intersect_key(DateHelper::weekDays(TRUE), array_combine($days, $days)), t('or')),
        ]));
      }
    }
  }
}
