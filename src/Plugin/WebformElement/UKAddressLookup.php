<?php

namespace Drupal\localgov_forms\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Entity\WebformOptions;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'localgov_webform_uk_address' Webform element.
 *
 * @WebformElement(
 *   id = "localgov_webform_uk_address",
 *   label = @Translation("LocalGov address lookup"),
 *   description = @Translation("Provides a UK address lookup element."),
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
class UKAddressLookup extends WebformCompositeBase {

  /**
   * Declares and overrides properties.
   *
   * Declares these configurable properties:
   * - geocoder_plugins
   * - always_display_manual_address_entry_btn.
   *
   * Overrides the `title_display` property.  By default, composite elements
   * keep their title invisible.  We want it to be very much visible.
   *
   * @see Drupal\webform\Plugin\WebformElementBase::form()
   *
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {

    $parent_properties = parent::defineDefaultProperties();

    $parent_properties['geocoder_plugins'] = [];
    $parent_properties['always_display_manual_address_entry_btn'] = 'yes';
    $parent_properties['local_custodian_code'] = 0;

    // We are trying to select the "Default" title display setting which results
    // in a visible title.  But the "Default" option uses an empty string as its
    // key and providing an empty key here does nothing.  As a work-around, we
    // are using "default" which, while not among the available option keys,
    // does the job perfectly.
    $parent_properties['title_display'] = 'default';

    // Enable the custom error message field
    // for the address lookup field.
    $parent_properties['required_error'] = '';

    return $parent_properties;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, WebformSubmissionInterface $webform_submission) {
    $submission_data = $webform_submission->getData();
    $webform = $webform_submission->getWebform();
    foreach ($submission_data as $key => $value) {
      $webform_element = $webform->getElement($key);
      if ($webform_element['#type'] == 'localgov_webform_uk_address') {
        unset($submission_data[$key]['address_lookup']);
        $extra_elements = ['lat', 'lng', 'ward'];
        foreach ($extra_elements as $extra_element) {
          unset($submission_data[$extra_element]);
        }
      }
    }
    $webform_submission->setData($submission_data);
  }

  /**
   * Webform element config form.
   *
   * Adds settings for:
   * - Selecting Geocoder plugins for address lookup.
   * - Deciding whether to display the manual address entry button at all times.
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $parent_form = parent::form($form, $form_state);

    $parent_form['element']['geocoder_plugins'] = [
      '#type'     => 'checkboxes',
      '#title'    => $this->t('Geocoder plugins'),
      '#options'  => \Drupal::service('localgov_forms.geocoder_selection')->listInstalledPluginNames(),
      '#required' => TRUE,
      '#description' => $this->t('These plugins are used for address lookup.  They are added from Configuration > System > Geocoder > Providers.'),
    ];

    $parent_form['element']['always_display_manual_address_entry_btn'] = [
      '#type'        => 'radios',
      '#title'       => 'When to display the manual address entry button',
      '#description'   => $this->t('Either display at all times or only after an address search.'),
      '#options'     => [
        'yes' => $this->t('Always'),
        'no'  => $this->t('After an address search'),
      ],
    ];

    // Restrict address lookup to a certain local authority.
    if ($local_custodian_webform_options = WebformOptions::load(self::LOCAL_CUSTODIAN_WEBFORM_OPTION_ENTITY_ID)) {
      $parent_form['element']['local_custodian_code'] = [
        '#type'        => 'select',
        '#title'       => $this->t('Local authority'),
        '#options'     => $local_custodian_webform_options->getOptions() ?: [],
        '#empty_value' => 0,
        '#description' => $this->t('Restricts address lookup to a single local authority.  The default behaviour is to lookup throughout the country.  This will override site-wide local custodian code setting on any Geocoder plugin.  This setting is only relevant for Geocoder plugins that support the local custodian code feature such as the "LocalGov OS Places" geocoder plugin.'),
      ];
    }

    return $parent_form;
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

    $full_address_line = ($value['address_1'] ? $value['address_1'] : '') .
      ($value['address_2'] ? ' ' . $value['address_2'] : '') .
      ($value['town_city'] ? ' ' . $value['town_city'] : '') .
      ($value['postcode'] ? ' ' . $value['postcode'] : '');
    $lines = $full_address_line ? [$full_address_line] : [];
    return $lines;
  }

  /**
   * Webform Options entity for listing Local custodian code.
   */
  const LOCAL_CUSTODIAN_WEBFORM_OPTION_ENTITY_ID = 'local_custodian_codes_gb';

}
