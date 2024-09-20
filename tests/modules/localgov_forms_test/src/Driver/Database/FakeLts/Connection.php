<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_test\Driver\Database\FakeLts;

use Drupal\Tests\Core\Database\Stub\StubConnection;
use Drupal\Tests\Core\Database\Stub\StubPDO;

/**
 * A stub of the abstract Connection class for testing purposes.
 *
 * Includes minimal implementations of Connection's abstract methods.
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
