<?php

namespace Drupal\localgov_forms_lts\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\localgov_forms_lts\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config form for storing Webform submissions in the LTS database.
 */
class LTSSettingsForm extends ConfigFormBase {

  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form[Constants::LTS_CONFIG_COPY_STATE] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Activate?'),
      '#description'   => $this->t('Activates copying Webform submissions to the Long Term Storage (LTS) database.'),
      '#config_target' => Constants::LTS_CONFIG_ID . ':' . Constants::LTS_CONFIG_COPY_STATE,
      '#options'       => [
        TRUE  => $this->t('Yes'),
        FALSE => $this->t('No'),
      ],
    ];

    $pii_redactor_plugin_id_list = $this->optionalPIIRedactorPluginManager ? array_map(fn(array $def): string => $def['label'], $this->optionalPIIRedactorPluginManager->getDefinitions()) : [];
    $form[Constants::LTS_CONFIG_PII_REDACTOR_PLUGIN_ID] = [
      '#type'          => 'select',
      '#title'         => $this->t('PII redactor plugin'),
      '#description'   => $this->t('Select a plugin to redact Personally Identifiable Information (PII) while copying to LTS database.'),
      '#config_target' => Constants::LTS_CONFIG_ID . ':' . Constants::LTS_CONFIG_PII_REDACTOR_PLUGIN_ID,
      '#options'       => $pii_redactor_plugin_id_list,
      '#empty_value'   => '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'localgov_forms_lts_settings';
  }

  /**
   * Keeps track of the optional PII redactor plugin manager.
   */
  public function __construct(protected ?PluginManagerInterface $optionalPIIRedactorPluginManager) {}

  /**
   * Factory.
   *
   * If the PII redactor plugin manager is available, passes it to the
   * constructor.
   */
  public static function create(ContainerInterface $container) {

    $pii_redactor_plugin_manager = $container->has(Constants::PII_REDACTOR_PLUGIN_MANAGER) ? $container->get(Constants::PII_REDACTOR_PLUGIN_MANAGER) : NULL;

    return new static($pii_redactor_plugin_manager);
  }

}
