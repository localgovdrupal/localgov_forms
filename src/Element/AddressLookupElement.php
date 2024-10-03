<?php

namespace Drupal\localgov_forms\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElementBase;

/**
 * Provides a central hub address lookup element.
 *
 * @FormElement("localgov_forms_address_lookup")
 */
class AddressLookupElement extends FormElementBase {

  /**
   * Static search string.
   *
   * @var string
   */
  public static $searchString;

  /**
   * Static address type.
   *
   * @var string
   */
  public static $addressType;

  /**
   * Static address results.
   *
   * @var array
   */
  public static $addressResults;

  /**
   * Static select element array.
   *
   * @var array
   */
  public static $selectElement;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input'                      => TRUE,
      '#tree'                       => TRUE,
      '#process'                    => [
        [$class, 'processAddressLookupElement'],
      ],
      '#address_search_description' => '',
      '#address_select_title'       => '',
      '#geocoder_plugins'           => [],
      '#local_custodian_code'       => 0,
      '#always_display_manual_address_entry_btn' => 'yes',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function processAddressLookupElement(&$element, FormStateInterface $form_state, &$form) {
    // Generate a unique ID that can be used by #states.
    $html_id = Html::getUniqueId('localgov_forms_address_lookup');
    $name = $element['#name'];
    $elem_parents = $element['#parents'];
    $elem_array_parents = $element['#array_parents'];

    $element['#prefix'] = '<div id="' . $html_id . '" class="centralhub-address-lookup js-centralhub-address-lookup">';
    $element['#suffix'] = '</div>';

    $element['address_search'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['js-address-search-container'],
      ],
      '#tree' => TRUE,
    ];

    $element['address_search']['address_searchstring'] = [
      '#type' => 'textfield',
      '#title' => t('Postcode or Street'),
      '#description' => $element['#address_search_description'] ?? t('Enter the postcode&hellip;'),
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
      '#attributes' => [
        'class' => ['js-address-searchstring'],
      ],
    ];

    $element['address_search']['address_actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'form-element-actions', 'address-actions', 'js-address-actions',
        ],
      ],
      '#tree' => TRUE,
    ];

    $element['address_search']['address_actions']['address_searchbutton'] = [
      '#name' => $name . '[address_search][address_actions][address_searchbutton]',
      '#parents' => array_merge($elem_parents, [
        'address_search', 'address_actions', 'address_searchbutton',
      ]),
      '#array_parents' => array_merge($elem_array_parents, [
        'address_search', 'address_actions', 'address_searchbutton',
      ]),
      '#type' => 'button',
      '#value' => t('Find address'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [
          'Drupal\localgov_forms\Element\AddressLookupElement', 'loadAddresses',
        ],
        'disable-refocus' => TRUE,
        'event' => 'click',
        // This element is updated with this AJAX callback.
        'wrapper' => $html_id . '--edit-address-options',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Loading addresses...'),
        ],
        'method' => 'html',
      ],
      '#attributes' => [
        'class' => ['address-searchbutton', 'js-address-searchbutton', 'btn'],
      ],
    ];

    $element['address_select'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $html_id . '--edit-address-options',
        'class' => [
          'address-select-container', 'js-address-select-container', 'margin-top--20',
        ],
        'aria-live' => 'polite',
      ],
      '#tree' => TRUE,
    ];

    $element['address_select']['address_select_list'] = [
      '#type' => 'select',
      '#title' => $element['#address_select_title'] ?? t('Select the address'),
      '#options' => [],
      '#empty_option' => '-' . t('Please choose an address') . '-',
      '#empty_value' => 0,
      '#default_value' => NULL,
      '#attributes' => [
        'class' => ['js-address-select'],
      ],
      '#address_type' => $element['#address_type'] ?? 'residential',
    ];

    if ($form_state->isProcessingInput()) {

      // Fix the select box form options.
      $parents = $element['#parents'];
      $array_parents = $element['#array_parents'];

      // Get form values.
      $form_values = $form_state->getValues();

      // Extract the parent form container.
      $parent_container = $form;
      foreach ($array_parents as $keyval) {
        $parent_container = $parent_container[$keyval];
      }

      // Extract the parent values form container.
      $parent_container_values = $form_values;
      foreach ($parents as $keyval) {
        $parent_container_values = $parent_container_values[$keyval];
      }
      if (!empty($parent_container_values['address_search']['address_searchstring'])) {
        $address_search = $parent_container_values['address_search']['address_searchstring'];
      }
      elseif (!empty($parent_container['address_search']['address_searchstring']['#value'])) {
        $address_search = $parent_container['address_search']['address_searchstring']['#value'];
      }
      elseif (!empty($parent_container['address_search']['address_searchstring']['#default_value'])) {
        $address_search = $parent_container['address_search']['address_searchstring']['#default_value'];
      }

      // Set the values of the select element.
      // This is required here as otherwise a form error of invalid value will
      // be shown when submitting the form.
      if (!empty($address_search)) {
        $element['address_select']['address_select_list'] = self::addressSelectLookup($address_search, $parent_container);

        if ($element['address_select']['address_select_list']['#type'] != 'markup') {
          $element['address_select']['address_select_list']['#disabled'] = FALSE;
          $options = array_keys($element['address_select']['address_select_list']['#options']);
          $selected = $parent_container_values['address_select']['address_select_list'] ?? 0;
          if (!in_array($selected, $options, TRUE)) {
            $element['address_select']['address_select_list']['#value'] = NULL;
          }
        }
        self::$selectElement = $element['address_select']['address_select_list'];
      }

      if (empty($element['address_select']['address_select_list']['#options']) && $element['address_select']['address_select_list']['#type'] != 'markup') {
        $element['address_select']['#attributes']['class'][] = 'hidden';
      }
    }

    // Attach javascript to populate the address fields.
    $element['#attached']['library'][] = 'localgov_forms/localgov_forms.address_change';

    return $element;
  }

  /**
   * Replace the address select box.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Form array element to replace the select element.
   */
  public static function loadAddresses(array $form, FormStateInterface &$form_state) {

    // Rebuild the form.
    $form_state->setRebuild();

    // Get the triggering element.
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#parents'], 0, -3);
    $array_parents = array_slice($triggering_element['#array_parents'], 0, -3);

    // Get form values.
    $form_values = $form_state->getUserInput();

    // Extract the parent form container.
    $parent_container = $form;
    foreach ($array_parents as $keyval) {
      $parent_container = $parent_container[$keyval];
    }

    // Extract the parent values form container.
    $parent_container_values = $form_values;
    foreach ($parents as $keyval) {
      $parent_container_values = $parent_container_values[$keyval];
    }

    // Run the address lookup and get the new select list.
    $address_search = $parent_container_values['address_search']['address_searchstring'];
    // @todo find out why we have to call this twice and avoid
    self::$selectElement = self::addressSelectLookup($address_search, $parent_container);

    return self::$selectElement;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (empty($input['address_search']['address_searchstring'])) {
      return NULL;
    }

    // FIXME: Call to method valueCallback() of deprecated class
    // Drupal\Core\Render\Element\FormElement: in drupal:10.3.0 and
    // is removed from drupal:12.0.0.
    // use \Drupal\Core\Render\Element\FormElementBase instead.
    // @phpstan-ignore-next-line
    return parent::valueCallback($element, $input, $form_state);
  }

  /**
   * Address select lookup with new values.
   *
   * @param string $address_search
   *   Search string.
   * @param array $address_element
   *   The address element in the form.
   *
   * @return array
   *   Replacement select list, or some markup for an error message.
   */
  public static function addressSelectLookup(string $address_search, array $address_element): array {
    $parent_container_id = $address_element['#id'];
    if (empty($address_search)) {
      $address_element['address_select']['address_select_list']['#prefix'] = '<div class="hidden">';
      $address_element['address_select']['address_select_list']['#suffix'] = '</div>';
      $address_element['address_select']['error'] = [
        '#type' => 'markup',
        '#markup' => '<p class="localgov-forms-alert localgov-forms-alert-info js-address-error">Please enter a postcode to search.</p>',
      ];
      return $address_element['address_select']['error'];
    }

    // Get the address type to lookup.
    $address_type = $address_element['address_select']['address_select_list']['#address_type'];

    // Do address lookup.
    // If its searching for the same address, return the static version.
    // Else make a new request.
    // This is to avoid multiple api lookup calls.
    if ($address_search !== self::$searchString || $address_type !== self::$addressType) {
      $selected_plugin_ids  = $address_element['#geocoder_plugins'];
      $local_custodian_code = $address_element['#local_custodian_code'];
      self::$addressResults = \Drupal::service('localgov_forms.address_lookup')->search([$address_search], $selected_plugin_ids, $local_custodian_code);
    }
    $address_list = self::$addressResults;

    if (empty($address_list)) {
      $address_element['address_select']['address_select_list']['#options'] = [];
      $address_element['address_select']['address_select_list']['#prefix'] = '<div class="hidden">';
      $address_element['address_select']['address_select_list']['#suffix'] = '</div>';
      $address_element['address_select']['error'] = [
        '#type' => 'markup',
        '#markup' => '<p class="localgov-forms-alert localgov-forms-alert-failure js-address-error">No addresses found</p>',
      ];
      return $address_element['address_select']['error'];

    }
    else {
      if (!empty($address_element['address_select']['address_select_list']['#empty_option'])) {
        $empty_option = $address_element['address_select']['address_select_list']['#empty_option'];
        $empty_value = $address_element['address_select']['address_select_list']['#empty_value'];
        $address_element['address_select']['address_select_list']['#options'][$empty_value] = $empty_option;
      }
      foreach ($address_list as $address) {
        $address_element['address_select']['address_select_list']['#options'][$address['name']] = $address['display'];
      }
      $address_element['address_select']['address_select_list']['#disabled'] = FALSE;
    }
    $address_element['address_select']['address_select_list']['#attached']['drupalSettings']['centralHub']['addressList'] = $address_list;
    $address_element['address_select']['address_select_list']['#attached']['drupalSettings']['centralHub'][$parent_container_id]['addressList'] = $address_list;
    $address_element['address_select']['address_select_list']['#attributes']['data-address-id'] = $parent_container_id;

    // Add latch to indicate this is a new search.
    $address_element['address_select']['address_select_list']['#attached']['drupalSettings']['centralHub']['newSearch'] = TRUE;

    // Remove the address select error.
    unset($address_element['address_select']['address_select_list']['#errors']);
    unset($address_element['address_select']['address_select_list']['#prefix']);
    unset($address_element['address_select']['address_select_list']['#suffix']);
    unset($address_element['address_select']['error']);

    self::$searchString = $address_search;
    self::$addressType  = $address_type;

    return $address_element['address_select']['address_select_list'];
  }

}
