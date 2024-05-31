<?php

namespace Drupal\localgov_forms_lts;

use Drupal\webform\WebformSubmissionStorage;
use Drupal\Core\Database\Connection as DbConnection;
use Drupal\Core\Database\Database;

/**
 * Alternate storage class for Webform submission.
 *
 * - Saves copies of Webform submission entities in the given database instead
 *   of the default one.
 * - Disables persistent entity cache as LTS does not provide any.
 *
 * Usage:
 * @code
 * $lts_storage = LtsStorageForWebformSubmission::createInstance($container, $entity_type_definition);
 * $lts_storage->setLtsDatabaseConnection($lts_db_connection); // Optional.
 * $a_webform_submission = $lts_storage->load($a_webform_submission_id);
 * @endcode
 */
class LtsStorageForWebformSubmission extends WebformSubmissionStorage {

  /**
   * Constructor wrapper.
   *
   * - Switches to the LTS database.
   */
  public function __construct(...$args) {

    parent::__construct(...$args);

    $this->database = Database::getConnection(key: Constants::LTS_DB_KEY);
  }

  /**
   * Setter for database connection.
   */
  public function setDatabaseConnection(DbConnection $db_connection): void {

    $this->database = $db_connection;
  }

  /**
   * Getter for database connection.
   */
  public function getDatabaseConnection(): DbConnection {

    return $this->database;
  }

  /**
   * Disables persistent cache.
   *
   * Because we have not got any in LTS.
   */
  protected function getFromPersistentCache(array &$ids = NULL) {

    return [];
  }

  /**
   * See above.
   */
  protected function setPersistentCache($entities) {}

  /**
   * Customizes cache Ids for LTS.
   *
   * Although we have disabled persistent cache above, cache ids are still used
   * in static cache.
   */
  protected function buildCacheId($id) {

    return Constants::LTS_CACHE_ID_PREFIX . ":{$this->entityTypeId}:$id";
  }

  /**
   * {@inheritdoc}
   *
   * Names our custom entity query service that speaks to the LTS database.
   */
  protected function getQueryServiceName() {

    return Constants::LTS_ENTITY_QUERY_SERVICE;
  }

}
