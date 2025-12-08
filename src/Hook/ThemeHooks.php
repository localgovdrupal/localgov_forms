<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\WebformSubmissionForm;
use Drupal\webform\WebformThirdPartySettingsManager;

/**
 * Theme related hooks.
 */
class ThemeHooks {

  /**
   * @var array
   *   Element types to add (optional) to.
   */
  public static array $optionalTypes = [
    'checkboxes',
    'checkbox',
    'radios',
    'textfield',
    'select',
  ];

  /**
   * Construct a new class.
   *
   * @param \Drupal\webform\WebformThirdPartySettingsManager $webformThirdPartySettings
   *   Webform third party settings manager.
   */
  public function __construct(protected WebformThirdPartySettingsManager $webformThirdPartySettings) {
  }

  #[Hook('webform_admin_third_party_settings_form_alter')]
  public function webformAdminForm(&$form, FormStateInterface $form_state) {
    $form['third_party_settings']['localgov_forms'] = [
      '#type' => 'details',
      '#title' => new TranslatableMarkup('LocalGov Forms'),
    ];
    $form['third_party_settings']['localgov_forms']['mark_optional'] = [
      '#type' => 'checkbox',
      '#title' => new TranslatableMarkup("Add '(optional)' to non-required elements"),
      '#description' => new TranslatableMarkup('If checked GDS forms style addition to the label title.'),
      '#default_value' => $this->webformThirdPartySettings->getThirdPartySetting('localgov_forms', 'mark_optional') ?: FALSE,
    ];
  }

  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$types): void {
    if ($this->webformThirdPartySettings->getThirdPartySetting('localgov_forms', 'mark_optional') ?: FALSE) {
      foreach (static::$optionalTypes as $type) {
        if (isset($types[$type])) {
          $types[$type]['#after_build'][] = [static::class, 'optionalElement'];
        }
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

      // Seems conditionally required will trigger this,
      // if default required, it's then disable with JS.
      if (
        $element['#required'] === FALSE &&
        isset($element['#title'])
      ) {
        $element['#title'] .= ' <span class="localgov-form-optional">'
          . new TranslatableMarkup('(optional)')
          . '</span>';
        $element['#attached']['library'][] = 'localgov_forms/localgov_forms.state';
      }
    }

    return $element;
  }

}
