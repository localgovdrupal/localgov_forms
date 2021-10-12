<?php

namespace Drupal\localgov_forms;

use Drupal\Core\Form\FormStateInterface;

/**
 * Utility class with helpers for webform.
 */
class WebformHelper {

  /**
   * Determine if an element is visible by checking visiblity of parents.
   *
   * A helper around
   * \Drupal::service('webform_submission.conditions_validator')->isElementVisible.
   *
   * @param array $element
   *   Webform element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param array $complete_form
   *   Form array.
   *
   * @return bool
   *   True if all parent elements and the element itself if visible,
   *   else FALSE.
   */
  public static function isElementVisibleThroughParent(array $element, FormStateInterface $form_state, array &$complete_form) {
    // Build webform submission and get conditions validator service.
    $form_object = $form_state->getFormObject();
    $webform_submission = $form_object->buildEntity($complete_form, $form_state);
    $conditions_validator = \Drupal::service('webform_submission.conditions_validator');

    // Loop through each of parents elements to work out visibility.
    $parents = $element['#webform_parents'];
    $parent_container = $complete_form['elements'];
    foreach ($parents as $parent) {
      $parent_container = $parent_container[$parent];
      $is_visible = $conditions_validator->isElementVisible($parent_container, $webform_submission);
      // Return FALSE as soon as an invisible element is found.
      // The element itself will be invisible if its parent is.
      if (!$is_visible) {
        return FALSE;
      }
    }

    // If all parents are visible, then return visibility of the element itself.
    return $conditions_validator->isElementVisible($element, $webform_submission);
  }

}
