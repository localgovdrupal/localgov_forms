<?php

namespace Drupal\localgov_forms\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\localgov_forms\WebformHelper;
use Drupal\webform\Element\WebformCompositeBase;
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
 * address_1, address_2, town_city, postcode, lat, lng, uprn, and ward.  The
 * address_lookup sub-element always return empty token values.  Example
 * token: [webform_submission:values:WEBFORM-ELEMENT-ID-GOES-HERE:uprn]
 *
 * @FormElement("localgov_webform_uk_address")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 */
class UKAddressLookup extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    return parent::getInfo() + ['#theme' => 'localgov_forms_uk_address_lookup'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {

    $element_list = [];
    $element_list['address_lookup'] = [
      '#type' => 'localgov_forms_address_lookup',
      '#address_type' => $element['#address_type'] ?? 'residential',
      '#address_search_description' => $element['#address_search_description'] ?? NULL,
      '#address_select_title' => $element['#address_select_title'] ?? NULL,
      '#geocoder_plugins' => $element['#geocoder_plugins'] ?? [],
      '#local_custodian_code' => $element['#local_custodian_code'] ?? 0,
      '#always_display_manual_address_entry_btn' => $element['#always_display_manual_address_entry_btn'] ?? 'yes',
    ];

    $element_list += WebformUKAddress::getCompositeElements($element);
    $element_list['address_1']['#prefix'] = '<div class="js-address-entry-container">';
    $element_list['postcode']['#suffix'] = '</div>';

    // Extras to store information for webform builders to access in
    // computed twig.
    // @See DRUP-1287.
    $extra_elements = ['lat', 'lng', 'uprn', 'ward'];
    foreach ($extra_elements as $extra_element) {
      $element_list[$extra_element] = [
        '#type' => 'hidden',
        '#default_value' => '',
        '#attributes' => [
          'class' => ['js-localgov-forms-webform-uk-address--' . $extra_element],
        ],
      ];
    }

    $element_list['#attached']['library'][] = 'localgov_forms/localgov_forms.address_select';
    $element_list['#attached']['drupalSettings']['centralHub']['isManualAddressEntryBtnAlwaysVisible'] = isset($element['#always_display_manual_address_entry_btn']) ? ($element['#always_display_manual_address_entry_btn'] === 'yes') : TRUE;

    return $element_list;
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

    // Temporarily reset the #limit_validation_errors property.  Otherwise we
    // can't safely set and manipulate errors below.
    //
    // @see Drupal\Core\Form\FormState::setErrorByName()
    // @see AddressLookupElement::processAddressLookupElement()
    $orig_limit_validation_errors = $form_state->getLimitValidationErrors();
    $form_state->setLimitValidationErrors(NULL);

    // If the element or any of its parent containers are hidden by conditions,
    // Bypass validation and clear any required element errors generated
    // for this element.
    if (!WebformHelper::isElementVisibleThroughParent($element, $form_state, $complete_form)) {
      $form_errors = $form_state->getErrors();
      $form_state->clearErrors();
      foreach ($form_errors as $error_key => $error_value) {
        if (strpos($error_key, $element_key . ']') === FALSE) {
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
    $is_any_address_line_required =
      !empty($element['#webform_composite_elements']['address_1']['#required']) ||
      !empty($element['#webform_composite_elements']['address_2']['#required']) ||
      !empty($element['#webform_composite_elements']['town_city']['#required']) ||
      !empty($element['#webform_composite_elements']['postcode']['#required']);

    $is_address_lookup_op = $form_state->getTriggeringElement()['#name'] === $element_key . '[address_lookup][address_search][address_actions][address_searchbutton]';

    if ($has_access && $is_any_address_line_required) {
      // If there is an address search, but no elements to select
      // (its a markup error)
      // Then show an error to search for a local address or select can't find
      // the address.
      if (!empty($search_string) && $element['address_lookup']['address_select']['address_select_list']['#type'] == 'markup') {
        $form_state->setError($element, t('Search for a local address, or select "Can\'t find the address" to enter an address.'));

        // Inline form errors don't work well for this element in this scenario.
        // This is because the Ajax callback attached to the `Find address`
        // button updates only part* of the address lookup element.  As a
        // result, any error set on any other part of the address lookup element
        // is lost.  To avoid this, we disable inline errors here.
        $complete_form['#disable_inline_form_errors'] = TRUE;
      }
      // Else if there is a search but no address selected,
      // set the select box as required.
      elseif (!empty($search_string) && empty($selected) && !$is_address_lookup_op) {
        WebformElementHelper::setRequiredError($element['address_lookup']['address_select']['address_select_list'], $form_state);
      }
      // Else mark the entire element as required.
      elseif (empty($search_string) && empty($selected)) {
        WebformElementHelper::setRequiredError($element['address_lookup']['address_search']['address_searchstring'], $form_state);

        // Inline form errors don't work well in this scenario.
        $complete_form['#disable_inline_form_errors'] = TRUE;
      }

      // Fetch errors, to allow any generated errors for the child elements
      // to be removed.
      $form_errors = $form_state->getErrors();

      // Loop through errors and remove child elements, except the select
      // and search query elements.
      foreach ($form_errors as $error_key => $error_value) {
        if (strpos($error_key, $element_key . ']') === 0 && ($error_key !== $element_key . '][address_lookup][address_select][address_select_list' && $error_key !== $element_key . '][address_lookup][address_search][address_searchstring')) {
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

    // Restore original value of the `limit_validation_errors` property now that
    // we are done with manipulating errors.
    $form_state->setLimitValidationErrors($orig_limit_validation_errors);

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
