<?php

namespace Drupal\localgov_forms_date\Element;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\Element\Datelist;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a datelist element.
 *
 * @FormElement("localgov_forms_date")
 */
class LocalgovFormsDate extends Datelist {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    return [
      '#input' => TRUE,
      '#element_validate' => [
        [static::class, 'useCustomErrorMessage'],
        [static::class, 'validateDatelist'],
        [static::class, 'areDatePartsNumeric'],
      ],
      '#process' => [
        [static::class, 'processDatelist'],
      ],
      '#theme' => 'datetime_form',
      '#theme_wrappers' => ['datetime_wrapper'],
      '#date_part_order' => ['day', 'month', 'year'],
      '#date_text_parts' => ['day', 'month', 'year'],
      '#date_year_range' => '1900:2050',
      '#date_increment' => 1,
      '#date_date_callbacks' => [],
      '#date_timezone' => date_default_timezone_get(),
    ];
  }

  /**
   * Wrapper over Datelist::valueCallback().
   *
   * Absorbs non-numeric day, month, or year values such as "1a", "dd", etc.
   * Such values leads PHP 8.x's checkdate() to throw TypeError exceptions
   * which core's Datelist class does not process gracefully yet.
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    try {
      return parent::valueCallback($element, $input, $form_state);
    }
    catch (\TypeError $e) {
      // This error msg will cancel any other error message added in
      // ::areDatePartsNumeric().
      // @see https://www.drupal.org/project/drupal/issues/2818437
      $form_state->setError($element, t('Non-numeric date parts detected.'));

      // Suppress PHP warning in Datelist::validateDatelist().
      $placeholder_date = DateTimePlus::createFromArray([], $element['#date_timezone']);
      $return = $input;
      $return['object'] = $placeholder_date;
      return $return;
    }
  }

  /**
   * Validation callback.
   *
   * Are all the parts of a date numeric?  There are three parts we are
   * concerned about here: day, month, and year.  If any of these are not
   * numeric, validation fails.  When this happens, we restore the date parts
   * to what was originally submitted.  Note that the date parts go through an
   * integer conversion before they reach validation.  The purpose of the
   * restoration is to bring back what was originally submitted.
   *
   * Example: "1A" is submitted as the "day" value.  This turns into *1* as part
   * of form processing.  "1A" fails validation, so we restore the day value to
   * "1A".  If we don't do this, the day value will render as "1" instead of
   * "1A" along with validation errors.
   */
  public static function areDatePartsNumeric(&$element, FormStateInterface $form_state, &$complete_form) :void {

    $err_msg                 = [];
    $unprocessed_day_input   = $element['#value']['day'] ?? '';
    $unprocessed_month_input = $element['#value']['month'] ?? '';
    $unprocessed_year_input  = $element['#value']['year'] ?? '';

    if (!empty($unprocessed_day_input) && !ctype_digit($unprocessed_day_input)) {
      $err_msg[] = 'Invalid day.';
    }
    if (!empty($unprocessed_month_input) && !ctype_digit($unprocessed_month_input)) {
      $err_msg[] = 'Invalid month.';
    }
    if (!empty($unprocessed_year_input) && !ctype_digit($unprocessed_year_input)) {
      $err_msg[] = 'Invalid year.';
    }

    if ($err_msg) {
      $form_state->setError($element, implode(' ', $err_msg));
      static::restoreUnprocessedDate($element);
    }
  }

  /**
   * Date input restoration.
   *
   * Returns date part values to the raw input.  This raw input has gone through
   * an integer conversion as part of form processing.  Here we restore the
   * original raw string values.
   */
  private static function restoreUnprocessedDate(array &$element) :void {

    if (isset($element['#value']['year'])) {
      $element['year']['#value'] = $element['#value']['year'];
    }

    if (isset($element['#value']['month'])) {
      $element['month']['#value'] = $element['#value']['month'];
    }

    if (isset($element['#value']['day'])) {
      $element['day']['#value'] = $element['#value']['day'];
    }
  }

  /**
   *
   *
   *
   */

   public static function useCustomErrorMessage(&$element, FormStateInterface $form_state, &$complete_form) {

    $title = $element['#title'];

    // If there's empty input and the field is required, set an error.
    if (empty($input['year']) && empty($input['month']) && empty($input['day']) && $element['#required']) {
      if (isset($element['#required_error'])) {
        $form_state->setError($element, t($element['#required_error']));
      } else {
      $form_state->setError($element, t('The %field date is required.', ['%field' => $title]));
      }
    }
  }

}

