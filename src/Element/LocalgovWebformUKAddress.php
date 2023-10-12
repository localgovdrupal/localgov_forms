<?php

namespace Drupal\localgov_forms\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\localgov_forms\WebformHelper;
use Drupal\webform\Utility\WebformElementHelper;

/**
 * Provides a 'localgov_webform_uk_address' form element.
 *
 * Webform composites contain a group of sub-elements.
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * Tokens for sub-elements of composite webform elements are available
 * from webform submissions.  For this element, available sub-elements include:
 * lat, lng, uprn, and ward.  The address_lookup and address_entry
 * sub-elements always return empty token values.  Example token:
 * [webform_submission:values:WEBFORM-ELEMENT-ID-GOES-HERE:uprn]
 *
 * @FormElement("localgov_webform_uk_address")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 */
class LocalgovWebformUKAddress extends WebformUKAddress {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    return parent::getInfo() + ['#theme' => 'localgov_webform_uk_address'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {

    $elements['address_lookup'] = [
      '#type' => 'localgov_forms_address_lookup',
      '#address_type' => $element['#address_type'] ?? 'residential',
      '#address_search_description' => $element['#address_search_description'] ?? NULL,
      '#address_select_title' => $element['#address_select_title'] ?? NULL,
      '#geocoder_plugins' => $element['#geocoder_plugins'] ?? [],
      '#always_display_manual_address_entry_btn' => $element['#always_display_manual_address_entry_btn'] ?? 'yes',
    ];

    $elements['address_entry'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['js-address-entry-container'],
      ],
      '#tree' => TRUE,
    ] + parent::getCompositeElements($element);

    if (!empty($element['#webform_composite_elements']['address_entry']['#required'])) {
      $elements['address_entry']['address_1']['#required'] = TRUE;
      $elements['address_entry']['town_city']['#required'] = TRUE;
      $elements['address_entry']['postcode']['#required'] = TRUE;
    }

    // Extras to store information for webform builders to access in
    // computed twig.
    // @See DRUP-1287.
    $extra_elements = ['lat', 'lng', 'uprn', 'ward'];
    foreach ($extra_elements as $extra_element) {
      $elements[$extra_element] = [
        '#type' => 'hidden',
        '#default_value' => '',
        '#attributes' => [
          'class' => ['js-localgov-forms-webform-uk-address--' . $extra_element],
        ],
      ];
    }

    $elements['#attached']['library'][] = 'localgov_forms/localgov_forms.address_select';
    $elements['#attached']['drupalSettings']['centralHub']['isManualAddressEntryBtnAlwaysVisible'] = isset($element['#always_display_manual_address_entry_btn']) ? ($element['#always_display_manual_address_entry_btn'] === 'yes') : TRUE;

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateWebformComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    // IMPORTANT: Must get values from the $form_states since sub-elements
    // may call $form_state->setValueForElement() via their validation hook.
    // @see \Drupal\webform\Element\WebformEmailConfirm::validateWebformEmailConfirm
    // @see \Drupal\webform\Element\WebformOtherBase::validateWebformOther
    $value = NestedArray::getValue($form_state->getValues(), $element['#parents']);
    $element_key = end($element['#parents']);

    // Guard check if the element itself is invisible to drupal.
    // This seems to be required when there are multiple address lookup Elements
    // on a webform that are hidden by conditions.
    // @see DRUP-1153.
    if (!Element::isVisibleElement($element)) {
      return;
    }

    // If the element or any of its parent containers are hidden by conditions,
    // Bypass validation and clear any required element errors generated
    // for this element.
    if (!WebformHelper::isElementVisibleThroughParent($element, $form_state, $complete_form)) {
      $form_errors = $form_state->getErrors();
      $form_state->clearErrors();
      foreach ($form_errors as $error_key => $error_value) {
        if (strpos($error_key, $element_key . ']') !== 0) {
          $form_state->setErrorByName($error_key, $error_value);
        }
      }
      return;
    }

    // Get the search string and selected value.
    $search_string = $value['address_lookup']['address_search']['address_searchstring'];
    $selected = $value['address_lookup']['address_select']['address_select_list'] ?? [];

    // Check to see if there are values in the address element form.
    $has_address_values = FALSE;
    foreach ($value as $indv_key => $indv_element) {
      if ($indv_key != 'address_lookup' && !is_array($indv_element)) {
        if (!empty($indv_element)) {
          $has_address_values = TRUE;
        }
      }
    }

    // If the select is empty, and the manual address elements are filled in,
    // validate the parent element.
    if (empty($selected) && $has_address_values) {
      // Clear the address search string.
      // This is to avoid the select box maintaing a value
      // (it's cleared if search string is empty).
      // @See DRUP-1185.
      $form_state->setValueForElement($element['address_lookup']['address_search']['address_searchstring'], NULL);
      return parent::validateWebformComposite($element, $form_state, $complete_form);
    }

    // Only validate composite elements that are visible.
    $has_access = (!isset($element['#access']) || $element['#access'] === TRUE);
    if ($has_access) {
      // If the address entry element is required.
      if (!empty($element['#webform_composite_elements']['address_entry']['#required'])) {
        // If there is an address search, but no elements to select
        // (its a markup error)
        // Then show an error to search for a local address or select can't find
        // the address.
        if (!empty($search_string) && $element['address_lookup']['address_select']['address_select_list']['#type'] == 'markup') {
          $form_state->setError($element, t('Search for a local address, or select "Can\'t find the address" to enter an address.'));
        }
        // Else if there is a search but no address selected,
        // set the select box as required.
        elseif (!empty($search_string) && empty($selected)) {
          WebformElementHelper::setRequiredError($element['address_lookup']['address_select']['address_select_list'], $form_state);
        }
        // Else mark the entire element as required.
        elseif (empty($search_string) && empty($selected)) {
          WebformElementHelper::setRequiredError($element, $form_state);
        }

        // Fetch errors, to allow any generated errors for the child elements
        // to be removed.
        $form_errors = $form_state->getErrors();

        // Loop through errors and remove child elements, except the select
        // element.
        foreach ($form_errors as $error_key => $error_value) {
          if (strpos($error_key, $element_key . ']') === 0 && $error_key != $element_key . '][address_lookup][address_select][address_select_list') {
            unset($form_errors[$error_key]);
          }
        }

        // If the search string and the select is empty, also remove the select
        // error.
        if (empty($search_string) && empty($selected)) {
          unset($form_errors[$element_key . '][address_lookup][address_select][address_select_list']);
        }

        // Reset form errors and reset them with the cleaned ones.
        $form_state->clearErrors();
        foreach ($form_errors as $error_key => $error_value) {
          $form_state->setErrorByName($error_key, $error_value);
        }
      }
    }

    // Clear empty composites value.
    if (empty(array_filter($value))) {
      $element['#value'] = NULL;
      $form_state->setValueForElement($element, NULL);
    }

    // Clear the address search string.
    // This is to avoid the select box maintaing a value
    // (it's cleared if search string is empty).
    // @See DRUP-1185.
    $form_state->setValueForElement($element['address_lookup']['address_search']['address_searchstring'], NULL);
  }

}
