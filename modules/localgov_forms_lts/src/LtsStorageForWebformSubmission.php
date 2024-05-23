<?php

namespace Drupal\localgov_forms_lts;

use Drupal\webform\WebformSubmissionStorage;
use Drupal\Core\Database\Connection as DbConnection;
use Drupal\Core\Database\Database;
use Drupal\localgov_forms_lts\Constants;

/**
 * Alternate storage class for Webform submission.
 *
 * Saves copies of Webform submission entities in the given database instead of
 * the default one.
 *
 * Usage:
 * @code
 * $lts_storage = LtsStorageForWebformSubmission::createInstance($container, $entity_type_definition);
 * $lts_storage->setLtsDatabaseConnection($lts_db_connection);
 * $a_webform_submission = $lts_storage->load($a_webform_submission_id);
 * @endcode
 */
class LtsStorageForWebformSubmission extends WebformSubmissionStorage {

  /**
   * Constructor wrapper.
   *
   * Switches to the LTS database.
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
   * {@inheritdoc}
   *
   * Names our custom entity query service that speaks to the LTS database.
   */
  protected function getQueryServiceName() {

    return Constants::LTS_ENTITY_QUERY_SERVICE;
  }

}
