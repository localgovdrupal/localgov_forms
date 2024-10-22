<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Copies Webform submissions to Long term storage.
 */
class LtsCopy implements ContainerInjectionInterface {

  /**
   * Copies Webform submissions to Long term storage.
   *
   * Copies Webform submissions added or edited since the last copy
   * operation.  Redacts Personally Identifiable Information (PII) from Webform
   * submissions before copying.
   *
   * By default, 50 Webform submissions are copied.
   */
  public function copy(int $count = Constants::COPY_LIMIT) :array {

    $last_copied_webform_sub_id = $this->findLastCopiedSubId();
    $webform_subs_to_copy       = $this->findCopyTargets($count);

    $copy_results = [];
    $has_copied   = FALSE;
    foreach ($webform_subs_to_copy as $webform_sub_id) {
      $is_new_webform_sub = $webform_sub_id > $last_copied_webform_sub_id;
      $copy_results[$webform_sub_id] = $this->copySub((int) $webform_sub_id, $is_new_webform_sub);
      $has_copied = TRUE;
    }

    if ($has_copied) {
      $this->setLatestUpdateTimestamp($copy_results);
    }

    return $copy_results;
  }

  /**
   * Saves a single webform submission in LTS database.
   */
  public function copySub(int $webform_sub_id, bool $is_new_webform_sub) :bool {

    $webform_sub = $this->webformSubStorage->load($webform_sub_id);
    PIIRedactor::redact($webform_sub);

    $db_connection = $this->ltsStorage->getDatabaseConnection();
    $tx = $db_connection->startTransaction();

    try {
      if ($is_new_webform_sub) {
        $this->ltsStorage->resave($webform_sub->enforceIsNew());
      }
      else {
        $this->ltsStorage->resave($webform_sub);
      }
    }
    catch (\Exception $e) {
      $tx->rollBack();

      $this->ltsLogger->error('Failed to add/edit Webform submission: %sub-id', [
        '%sub-id' => $webform_sub_id,
      ]);

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Finds last copied Webform submission's id.
   */
  public function findLastCopiedSubId() :int {

    $last_copied_webform_sub_id_raw = $this->ltsStorage->getAggregateQuery()
      ->accessCheck(FALSE)
      ->aggregate('sid', 'MAX')
      ->execute();
    $last_copied_webform_sub_id = $last_copied_webform_sub_id_raw[0]['sid_max'] ?? 0;
    return (int) $last_copied_webform_sub_id;
  }

  /**
   * Finds Webform submissions to copy.
   *
   * These are the Webform submissions that have been added or edited since the
   * last copy operation.
   *
   * For offset to work, *both* parameters must be provided with nonnegative
   * values.
   */
  public function findCopyTargets(int $count = -1) :array {

    $last_copied_webform_sub_changed_ts = $this->findLatestUpdateTimestamp();

    $webform_subs_to_copy_query = $this->webformSubStorage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('changed', $last_copied_webform_sub_changed_ts, '>')
      ->condition('in_draft', 0)
      ->sort('changed');

    if ($count > -1) {
      $webform_subs_to_copy_query->range(start: 0, length: $count);
    }

    $copy_targets = $webform_subs_to_copy_query->execute();
    return $copy_targets;
  }

  /**
   * When did the last copied Webform submission change?
   */
  public function findLatestUpdateTimestamp() :int {

    $ts = (int) $this->ltsKeyValueStore->get(Constants::LAST_CHANGE_TIMESTAMP, default: 0);
    return $ts;
  }

  /**
   * Records time of latest copy operation.
   */
  public function setLatestUpdateTimestamp(array $copy_results) :void {

    $last_copied_webform_sub_id = array_key_last($copy_results);
    $last_copied_webform_sub = $this->webformSubStorage->load($last_copied_webform_sub_id);
    $this->ltsKeyValueStore->set(Constants::LAST_CHANGE_TIMESTAMP, $last_copied_webform_sub->getChangedTime());
  }

  /**
   * Constructor.
   *
   * Keeps track of dependencies.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, KeyValueFactoryInterface $key_value_factory, LoggerChannelFactoryInterface $logger_factory, WebformSubmissionStorageInterface $lts_storage) {

    $this->webformSubStorage = $entity_type_manager->getStorage('webform_submission');
    $this->ltsStorage        = $lts_storage;
    $this->ltsKeyValueStore  = $key_value_factory->get(Constants::LTS_KEYVALUE_STORE_ID);
    $this->ltsLogger         = $logger_factory->get(Constants::LTS_LOGGER_CHANNEL_ID);
  }

  /**
   * Factory.
   */
  public static function create(ContainerInterface $container) :LtsCopy {

    $webform_sub_def = $container->get('entity_type.manager')->getDefinition('webform_submission');

    return new LtsCopy(
      $container->get('entity_type.manager'),
      $container->get('keyvalue'),
      $container->get('logger.factory'),
      LtsStorageForWebformSubmission::createInstance($container, $webform_sub_def)
    );
  }

  /**
   * Key value store for LTS related state.
   *
   * @var Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $ltsKeyValueStore;

  /**
   * Entity type manager service.
   *
   * @var Drupal\webform\WebformSubmissionStorageInterface
   */
  protected $webformSubStorage;

  /**
   * Database service for the Long term storage database.
   *
   * @var Drupal\webform\WebformSubmissionStorageInterface
   */
  protected $ltsStorage;

  /**
   * Logger channel.
   *
   * @var Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $ltsLogger;

}
