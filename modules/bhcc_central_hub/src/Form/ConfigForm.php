<?php

namespace Drupal\bhcc_central_hub\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * ConfigForm for central hub URL.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bhcc_central_hub.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bhcc_central_hub_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bhcc_central_hub.settings');
    $form['central_hub_service_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Central Hub Service URL'),
      '#description' => $this->t('The URL of the Central hub service to use.'),
      '#size' => 64,
      '#default_value' => $config->get('central_hub_service_url'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('bhcc_central_hub.settings')
      ->set('central_hub_service_url', $form_state->getValue('central_hub_service_url'))
      ->save();
  }

}
