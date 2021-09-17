<?php

namespace Drupal\localgov_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bhcc_central_hub\AddressLookupService;

/**
 * Class AddressPickerForm.
 */
class AddressPickerForm extends FormBase {

  /**
   * Drupal\bhcc_central_hub\AddressLookupServiceInterface definition.
   *
   * @var \Drupal\bhcc_central_hub\AddressLookupServiceInterface
   */
  protected $bhcc_central_hub_address_lookup;

  protected $address_type;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->bhcc_central_hub_address_lookup = $container->get('bhcc_central_hub.address_lookup');
    $instance->address_type = 'all';
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bhcc_address_picker_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['address_search_element'] = [
      '#type' => 'bhcc_central_hub_address_lookup',
      '#address_type' => $this->address_type,
      '#address_search_description' => $this->t('Enter your postcode or street name to search for your parking zone'),
      '#address_select_title' => $this->t('Select your address'),
    ];

    // $form['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Submit'),
    // ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing...
  }

}
