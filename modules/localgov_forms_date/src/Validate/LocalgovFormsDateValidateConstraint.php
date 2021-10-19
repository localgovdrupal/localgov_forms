<?php

namespace Drupal\localgov_forms_date\Validate;

use Drupal\Core\Form\FormStateInterface;

/**
 * Localgov Forms Date field Alpha numeric validation.
 */
class LocalgovFormsDateValidateConstraint {

  /**
   * Validate function.
   *
   * @param array $element
   *   An associative array containing properties of the date field elements.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   An associative array containing the structure of the form.
   */
  public static function validate(array &$element, FormStateInterface $form_state, array &$form) {

    $webformKey = $element['#webform_key'];
    $date_parts = $form_state->getValue($webformKey);

    // Check if the day field contains non numeric charcaters.
    if (!empty($date_parts['day']) && !is_numeric($date_parts['day'])) {
      $form_state->setError($element, t('The %field field must be a number.', ['%field' => t("day")]));
    }

    // Check if the month field contains non numeric charcaters.
    if (!empty($date_parts['month']) && !is_numeric($date_parts['month'])) {
      $form_state->setError($element, t('The %field field must be a number.', ['%field' => t("month")]));
    }

    // Check if the year field contains non numeric charcaters.
    if (!empty($date_parts['year']) && !is_numeric($date_parts['year'])) {
      $form_state->setError($element, t('The %field field must be a number.', ['%field' => t("year")]));
    }

  }

}
