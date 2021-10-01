<?php

namespace Drupal\localgov_forms\Plugin\WebformElement;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'bhcc_central_hub_webform_uk_address' element.
 *
 * @WebformElement(
 *   id = "bhcc_central_hub_webform_uk_address",
 *   label = @Translation("Localgov address lookup"),
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
class BHCCCentralHubWebformUKAddress extends BHCCWebformUKAddress {

  /**
   * {@inheritdoc}
   */
  public function preSave(array &$element, WebformSubmissionInterface $webform_submission) {
    $submission_data = $webform_submission->getData();
    $webform = $webform_submission->getWebform();
    foreach ($submission_data as $key => $value) {
      $webform_element = $webform->getElement($key);
      if ($webform_element['#type'] == 'bhcc_central_hub_webform_uk_address') {
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
   * Adds a settings for selecting Geocoder plugins for address lookup.
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $parent_form = parent::form($form, $form_state);

    // @see AddressLookupElement::getInfo()
    // @see WebformCompositeBase::defineDefaultProperties()
    $parent_form['element']['geocoder_plugins'] = [
      '#type'     => 'checkboxes',
      '#title'    => $this->t('Geocoder plugins'),
      '#options'  => \Drupal::service('localgov_forms.geocoder_selection')->listInstalledPluginNames(),
      '#required' => TRUE,
      '#description' => $this->t('These plugins are used for address lookup.  They are added from Configuration > System > Geocoder > Providers.'),
    ];

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

    $lines = [];
    $lines[] =
      ($value['address_1'] ? $value['address_1'] : '') .
      ($value['address_2'] ? ' ' . $value['address_2'] : '') .
      ($value['town_city'] ? ' ' . $value['town_city'] : '') .
      ($value['postcode'] ? ' ' . $value['postcode'] : '');
    return $lines;
  }

}
