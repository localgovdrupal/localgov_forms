<?php

namespace Drupal\bhcc_webform_date\Plugin\WebformElement;

use Drupal\Core\Datetime\DateHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'bhcc_webform_date' element.
 *
 * @WebformElement(
 *   id = "bhcc_webform_date",
 *   label = @Translation("BHCC Date"),
 *   description = @Translation("Provides a webform element example."),
 *   category = @Translation("Composite elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class BHCCWebformDate extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [
      // Form validation.
      'date_date_min' => '',
      'date_date_max' => '',
      'days_of_week' => ['0', '1', '2', '3', '4', '5', '6'],
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    $lines = [];
    $lines[] =
      ($value['year'] ? $value['year'] : '') .
      ($value['month'] ? '-' . $value['month'] : '') .
      ($value['day'] ? '-' . $value['day'] : '');
    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['date'] = [
      '#type' => 'details',
      '#title' => 'Date settings',
      '#open' => TRUE,
      '#weight' => -40,
    ];

    // Date min/max validation.
    // Copied from Drupal\webform\Plugin\WebformElementDateBase.
    $form['date']['date_container'] = $this->getFormInlineContainer() + [
      '#weight' => 10,
    ];
    $form['date']['date_container']['date_date_min'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date minimum'),
      '#description' => $this->t('Specifies the minimum date.')
        . ' ' . $this->t('To limit the minimum date to the submission date use the <code>[webform_submission:created:html_date]</code> token.')
        . '<br /><br />'
        . $this->t('Accepts any date in any <a href="https://www.gnu.org/software/tar/manual/html_chapter/tar_7.html#Date-input-formats">GNU Date Input Format</a>. Strings such as today, +2 months, and Dec 9 2004 are all valid.'),
    ];
    $form['date']['date_container']['date_date_max'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date maximum'),
      '#description' => $this->t('Specifies the maximum date.')
        . ' ' . $this->t('To limit the maximum date to the submission date use the <code>[webform_submission:created:html_date]</code> token.')
        . '<br /><br />'
        . $this->t('Accepts any date in any <a href="https://www.gnu.org/software/tar/manual/html_chapter/tar_7.html#Date-input-formats">GNU Date Input Format</a>. Strings such as today, +2 months, and Dec 9 2004 are all valid.'),
    ];


    // 04/05/2021
    // function override copied from Drupal\webform\Plugin\WebformElement\DateBase.php
    // Date days of the week validation.
    $form['date']['date_container']['days_of_week'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Date days of the week'),
      '#options' => DateHelper::weekDaysAbbr(TRUE),
      '#element_validate' => [['\Drupal\webform\Utility\WebformElementHelper', 'filterValues']],
      '#description' => $this->t('Specifies the day(s) of the week. Please note, the date picker will disable unchecked days of the week.'),
      '#options_display' => 'side_by_side',
      '#required' => TRUE,
      '#weight' => 20,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $properties = $this->getConfigurationFormProperties($form, $form_state);

    $has_error = FALSE;
    foreach($properties as $key => $value) {
      if (strpos($key, '#date_') === 0) {

        // Validate a proper date.

        // If no value, continue.
        if (empty($value)) {
          continue;
        }

        // Check if can be interpreted as a date.
        // From DateBase.
        $date_values = (array) $value;
        foreach ($date_values as $date_value) {

          // If not a token validate the date.
          // eg. [webform_submission:created:html_date]
          if (!preg_match('/^\[[^]]+\]$/', $date_value)) {

            // Replace slashes with dashes so we always validate a UK format.
            $date_value = str_replace('/', '-', $date_value);
            if (strtotime($date_value) === FALSE) {

              // mark form as having an error.
              $has_error = TRUE;

              // Set the error.
              $element_key = ltrim($key, '#');
              $t_args = [
                '@title' => $form['properties']['date']['date_container'][$element_key]['#title'],
              ];
              $form_state->setError($form['properties']['date']['date_container'][$element_key], $this->t('The @title could not be interpreted in <a href="https://www.gnu.org/software/tar/manual/html_chapter/tar_7.html#Date-input-formats">GNU Date Input Format</a>.', $t_args));
            }
          }
        }
      }
    }

    parent::validateConfigurationForm($form, $form_state);
    return !$has_error;
  }

}
