<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_test\Driver\Database\FakeLts;

use Drupal\Tests\Core\Database\Stub\StubConnection;
use Drupal\Tests\Core\Database\Stub\StubPDO;

/**
 * A mock Drupal database driver class.
 *
 * Useful during testing.
 *
 * Good enough to serve as a database connection object but cannot actually
 * perform any query operation yet.
 */
class Connection extends StubConnection {

  /**
   * {@inheritdoc}
   */
  public $driver = 'fake_lts';

  /**
   * {@inheritdoc}
   */
  public static function open(array &$connection_options = []) {
    return new StubPDO();
  }

}
