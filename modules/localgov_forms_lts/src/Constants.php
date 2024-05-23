<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts;

/**
 * Constants for this module.
 */
class Constants {

  /**
   * Used in settings.php for database declaration.
   */
  const LTS_DB_KEY = 'localgov_forms_lts';

  /**
   * Relevant key value store name.
   */
  const LTS_KEYVALUE_STORE_ID = 'localgov_forms_lts';

  /**
   * Relevant Logger channel.
   */
  const LTS_LOGGER_CHANNEL_ID = 'localgov_forms_lts';

  /**
   * When did the last copied Webform submission change?
   */
  const LAST_CHANGE_TIMESTAMP = 'last_copied_webform_sub_changed_ts';

  /**
   * LTS database-based Webform submission entity query service.
   */
  const LTS_ENTITY_QUERY_SERVICE = 'localgov_forms_lts.query.sql';

  /**
   * How many Webform submissions to copy at a time.
   *
   * Useful in batch jobs.
   */
  const COPY_LIMIT = 50;

}
