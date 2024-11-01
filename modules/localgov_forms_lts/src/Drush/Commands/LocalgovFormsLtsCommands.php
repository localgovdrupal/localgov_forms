<?php

namespace Drupal\localgov_forms_lts\Drush\Commands;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\localgov_forms_lts\Constants;
use Drupal\localgov_forms_lts\LtsCopy;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile for LTS management.
 *
 * Commands:
 * - copy: Copies all existing Webform submissions to the LTS.
 */
final class LocalgovFormsLtsCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Drush command to copy all Webform submissions to LTS.
   */
  #[CLI\Command(name: 'localgov_forms_lts:copy', aliases: ['forms-lts-copy'])]
  #[CLI\Option(name: 'force', description: 'Ignore copy disablement config and copy anyway.  Useful immediately after module installation.')]
  #[CLI\Usage(name: 'localgov_forms_lts:copy', description: 'Copies all existing Webform submissions.')]
  public function copy($options = ['force' => FALSE]) {

    if (!localgov_forms_lts_has_db()) {
      $this->logger->error(dt('The LocalGov Forms LTS database must exist for this Drush command to function.'));
      return;
    }

    $is_proceed = $options['force'] ?: $this->configFactory->get('localgov_forms_lts.settings')?->get('is_copy_active');
    if (!$is_proceed) {
      $this->logger->warning(dt('Copying is disabled in localgov_forms_lts module configuration.  Use --force to override.'));
      return;
    }

    $pii_redactor_plugin_id = $this->configFactory->get('localgov_forms_lts.settings')?->get('pii_redactor_plugin_id');
    $pii_redactor_plugin    = ($pii_redactor_plugin_id && $this->serviceContainer->has('plugin.manager.pii_redactor')) ? $this->serviceContainer->get('plugin.manager.pii_redactor')->createInstance($pii_redactor_plugin_id) : NULL;

    $lts_copy_obj = LtsCopy::create(\Drupal::getContainer(), $pii_redactor_plugin);
    $webform_sub_ids_to_copy = $lts_copy_obj->findCopyTargets();
    $batch_count = ceil(count($webform_sub_ids_to_copy) / Constants::COPY_LIMIT);

    $batch_builder = new BatchBuilder();
    $drupal_logger = $this->drupalLoggerFactory->get('localgov_forms_lts');
    for ($i = 0; $i < $batch_count; $i++) {
      $batch_builder->addOperation([self::class, 'copyInBatch'], [
        $pii_redactor_plugin,
        $drupal_logger,
      ]);
    }

    batch_set($batch_builder->toArray());
    drush_backend_batch_process();

    // Info messages are not appearing in the console, so settling for Notices.
    $this->logger->notice('Batch operation for copying Webform submissions to Long term storage ends.');
  }

  /**
   * Batch operation callback.
   *
   * Copies a fixed number of Webform submissions to LTS.
   */
  public static function copyInBatch(?PluginInspectionInterface $pii_redactor_plugin, LoggerInterface $drupal_logger, &$context) {

    $lts_copy_obj = LtsCopy::create(\Drupal::getContainer(), $pii_redactor_plugin);
    $copy_results = $lts_copy_obj->copy();

    $feedback = _localgov_forms_lts_prepare_feedback_msg($copy_results);
    $drupal_logger->info($feedback);

    $context['results'][] = $copy_results;
    $context['message'] = $feedback;
  }

  /**
   * Constructs a LocalgovFormsLtsCommands object.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerChannelFactoryInterface $drupalLoggerFactory,
    private readonly ContainerInterface $serviceContainer,
  ) {
    parent::__construct();
  }

}
