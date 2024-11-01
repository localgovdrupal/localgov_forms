<?php

namespace Drupal\localgov_forms_lts\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
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

    $form['is_copying_enabled'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Activate?'),
      '#description'   => $this->t('Activates copying Webform submissions to the Long Term Storage (LTS) database.'),
      '#config_target' => self::CONFIG_ID . ':is_copying_enabled',
      '#options'       => [
        TRUE  => $this->t('Yes'),
        FALSE => $this->t('No'),
      ],
    ];

    $pii_redactor_plugin_id_list = $this->optionalPIIRedactorPluginManager ? array_map(fn(array $def): string => $def['label'], $this->optionalPIIRedactorPluginManager->getDefinitions()) : [];
    $form['pii_redactor_plugin_id'] = [
      '#type'          => 'select',
      '#title'         => $this->t('PII redactor plugin'),
      '#description'   => $this->t('Select a plugin to redact Personally Identifiable Information (PII) while copying to LTS database.'),
      '#config_target' => self::CONFIG_ID . ':pii_redactor_plugin_id',
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

    $pii_redactor_plugin_manager = $container->has(self::PII_REDACTION_PLUGIN_MANAGER_ID) ? $container->get(self::PII_REDACTION_PLUGIN_MANAGER_ID) : NULL;

    return new static($pii_redactor_plugin_manager);
  }

  /**
   * Config settings.
   *
   * @var string
   */
  const CONFIG_ID = 'localgov_forms_lts.settings';

  const PII_REDACTION_PLUGIN_MANAGER_ID = 'plugin.manager.pii_redactor';

}
