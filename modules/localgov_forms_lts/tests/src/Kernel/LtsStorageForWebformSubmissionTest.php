<?php

namespace Drupal\Tests\localgov_forms_lts\Kernel;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection as DbConnection;
use Drupal\Core\Database\Query\Insert;
use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_forms_lts\LtsStorageForWebformSubmission;
use Drupal\localgov_forms_lts\Constants;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Tests LtsStorageForWebformSubmission's ability to store in LTS.
 *
 * When we are trying to store a Webform submission into the Long term storage,
 * we need to ensure that it is actually saved into the Long term storage
 * database and *not* into the default database.
 */
class LtsStorageForWebformSubmissionTest extends KernelTestBase {

  /**
   * Tests LTS database usage.
   *
   * - Creates a fresh Webform submission entity.
   * - Tries to save it into the LTS database.
   */
  public function testLtsDbUsage() {

    $contact2_webform = $this->container->get('entity_type.manager')->getStorage('webform')->load(self::TEST_WEBFORM_ID);
    $this->assertNotNull($contact2_webform);

    $a_webform_submission = WebformSubmission::create([
      'webform_id' => self::TEST_WEBFORM_ID,
      'data' => [
        'name'  => 'Foo Bar',
        'email' => 'foo@example.net',
      ],
    ]);

    // Temporary measure to satisfy
    // LtsStorageForWebformSubmission::__construct().
    // Gets overwritten by the call to $test_obj->setDatabaseConnection() below.
    Database::addConnectionInfo(Constants::LTS_DB_KEY, 'default', [
      'driver'    => 'fake_lts',
      'namespace' => 'Drupal\\localgov_forms_test\\Driver\\Database\\FakeLts',
    ]);
    $webform_sub_def = $this->container->get('entity_type.manager')->getDefinition('webform_submission');
    $test_obj = LtsStorageForWebformSubmission::createInstance($this->container, $webform_sub_def);
    $test_obj->setDatabaseConnection($this->mockLtsDbConnection);

    $test_obj->resave($a_webform_submission);
  }

  /**
   * Prepares the mock LTS database connection.
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installSchema('webform', ['webform']);
    $this->installConfig(['webform']);
    $this->installEntitySchema('user');

    $mock_insert_query = $this->createConfiguredMock(Insert::class, [
      'execute' => random_int(1, 10),
    ]);
    $mock_insert_query->method('fields')->willReturnSelf();

    $this->mockLtsDbConnection = $this->createMock(DbConnection::class);
    $this->mockLtsDbConnection->expects($this->exactly(self::WEBFORM_SUB_LTS_INSERT_COUNT))
      ->method('insert')
      ->willReturnMap([
        ['webform_submission', $mock_insert_query],
        ['webform_submission', [
          'return' => Database::RETURN_INSERT_ID,
        ], $mock_insert_query,
        ],
        ['webform_submission_data', $mock_insert_query],
        ['webform_submission_data', [], $mock_insert_query],
      ]);
  }

  /**
   * A new Webform submission is inserted into the LTS database twice.
   *
   * Once in the webform_submission table and then in the
   * webform_submission_data table.
   */
  const WEBFORM_SUB_LTS_INSERT_COUNT = 2;

  /**
   * A Webform from the localgov_forms_test module.
   */
  const TEST_WEBFORM_ID = 'contact';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'localgov_forms_test',
    'system',
    'user',
    'webform',
  ];

  /**
   * Mock LTS database connection.
   *
   * Is it being used at all?  That's what this test is about.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $mockLtsDbConnection;

}
