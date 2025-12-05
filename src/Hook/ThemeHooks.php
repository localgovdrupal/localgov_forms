<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\WebformSubmissionForm;

/**
 * Theme related hooks.
 */
class ThemeHooks {

  /**
   * @var array
   *   Element types to add (optional) to.
   */
  static array $optionalTypes = [
    'checkboxes',
    'checkbox',
    'radios',
    'textfield',
    'select',
  ];

  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$types): void {
    foreach (static::$optionalTypes as $type) {
      if (isset($types[$type])) {
        $types[$type]['#after_build'][] = [static::class, 'optionalElement'];
      }
    }
  }

  /**
   * After build callback.
   *
   * Add '(optional)' to appropriate non-required element titles.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The, potentially altered, form element.
   */
  static function optionalElement(array $element, FormStateInterface $form_state): array {
    if ($form_state->getFormObject() instanceof WebformSubmissionForm) {
      if ($element['#type'] === 'checkbox') {
        // If it is desired to add optional to single checkboxes there will be
        // a single parent with the same name as the checkbox in #parents.
        // A checkbox in a checkboxes list will have at least two parents.
        return $element;
      }

      if (
        $element['#required'] === FALSE &&
        isset($element['#title'])
      ) {
        $element['#title'] .= ' <span class="localgov-form-optional">'
          . new TranslatableMarkup('(optional)')
          . '</span>';
      }
    }

    return $element;
  }

}
