<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms_lts\Unit;

use Drupal\Core\Database\Connection as DbConnection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryAggregateInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\localgov_forms_lts\LtsCopy;
use Drupal\localgov_forms_lts\LtsStorageForWebformSubmission;
use Drupal\Tests\UnitTestCase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformSubmissionStorageInterface;

/**
 * Unit tests for LtsCopy.
 *
 * Tests a scenario where an existing Webform submission has been updated and
 * two new Webform submissions have been added since Webform submissions were
 * last copied to long term storage.
 */
class LtsCopyTest extends UnitTestCase {

  /**
   * Tests LtsCopy::copy().
   */
  public function testCopySub() {

    $test_obj = new LtsCopy($this->mockEntityTypeManager, $this->mockLtsKeyValueFactory, $this->mockLtsLoggerFactory, $this->mockLtsStorage);

    $copy_results = $test_obj->copy();

    $this->assertCount(expectedCount: self::WEBFORM_SUB_EXPECTED_COPY_COUNT, haystack: $copy_results);
  }

  /**
   * Creates mock dependencies.
   *
   * Initializes all objects needed to create an LtsCopy object.
   */
  public function setup(): void {

    parent::setup();

    $mock_webform_sub_storage = static::setupMockWebformSubmissionStorage();
    $this->mockEntityTypeManager = $this->createConfiguredMock(EntityTypeManagerInterface::class, [
      'getStorage' => $mock_webform_sub_storage,
    ]);

    $mock_lts_storage_query = $this->createMock(QueryAggregateInterface::class);
    $mock_lts_storage_query->method('execute')
      ->willReturn([['sid_max' => self::LAST_COPIED_WEBFORM_SUB_ID]]);
    $mock_lts_storage_query->method('accessCheck')->willReturnSelf();
    $mock_lts_storage_query->method('aggregate')->willReturnSelf();
    $this->mockLtsStorage = $this->createConfiguredMock(LtsStorageForWebformSubmission::class, [
      'getAggregateQuery'     => $mock_lts_storage_query,
      'getDatabaseConnection' => $this->createMock(DbConnection::class),
    ]);
    $this->mockLtsStorage->expects($this->exactly(self::WEBFORM_SUB_EXPECTED_COPY_COUNT))->method('resave');

    $this->mockLtsKeyValueFactory = $this->createConfiguredMock(KeyValueFactoryInterface::class, [
      'get' => $this->createMock(KeyValueStoreInterface::class),
    ]);

    $this->mockLtsLoggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
  }

  /**
   * Prepares a mock Webform submission storage object.
   *
   * When queried, returns three mock Webform submission entities.
   */
  public function setupMockWebformSubmissionStorage(): WebformSubmissionStorageInterface {
    $mock_webform_sub_query = $this->createMock(QueryInterface::class);
    $mock_webform_sub_query->expects($this->any())
      ->method('execute')
      ->willReturn([
        self::LAST_COPIED_WEBFORM_SUB_ID => (string) self::LAST_COPIED_WEBFORM_SUB_ID,
        self::NEW_WEBFORM_SUB_ID0 => (string) self::NEW_WEBFORM_SUB_ID0 ,
        self::NEW_WEBFORM_SUB_ID1 => (string) self::NEW_WEBFORM_SUB_ID1 ,
      ]);
    $mock_webform_sub_query->method('accessCheck')->willReturnSelf();
    $mock_webform_sub_query->method('condition')->willReturnSelf();
    $mock_webform_sub_query->method('sort')->willReturnSelf();
    $mock_webform_sub_storage = $this->createConfiguredMock(WebformSubmissionStorageInterface::class, [
      'getQuery' => $mock_webform_sub_query,
    ]);

    $mock_webform = $this->createConfiguredMock(WebformInterface::class, [
      'getElementsDecodedAndFlattened' => [],
    ]);
    $mock_existing_webform_sub = $this->createConfiguredMock(WebformSubmissionInterface::class, [
      'id'         => self::LAST_COPIED_WEBFORM_SUB_ID,
      'getWebform' => $mock_webform,
    ]);
    $mock_new_webform_sub0 = $this->createConfiguredMock(WebformSubmissionInterface::class, [
      'id'         => self::NEW_WEBFORM_SUB_ID0,
      'getWebform' => $mock_webform,
    ]);
    $mock_new_webform_sub0->expects($this->once())->method('enforceIsNew')->willReturnSelf();
    $mock_new_webform_sub1 = $this->createConfiguredMock(WebformSubmissionInterface::class, [
      'id'         => self::NEW_WEBFORM_SUB_ID1,
      'getWebform' => $mock_webform,
    ]);
    $mock_new_webform_sub1->expects($this->once())->method('enforceIsNew')->willReturnSelf();
    $mock_webform_sub_storage->expects($this->exactly(self::WEBFORM_SUB_EXPECTED_LOAD_COUNT))
      ->method('load')
      ->willReturnMap([
        [self::LAST_COPIED_WEBFORM_SUB_ID, $mock_existing_webform_sub],
        [self::NEW_WEBFORM_SUB_ID0, $mock_new_webform_sub0],
        [self::NEW_WEBFORM_SUB_ID1, $mock_new_webform_sub1],
      ]);

    return $mock_webform_sub_storage;
  }

  const LAST_COPIED_WEBFORM_SUB_ID = 99;

  const NEW_WEBFORM_SUB_ID0 = 100;

  const NEW_WEBFORM_SUB_ID1 = 101;

  const WEBFORM_SUB_EXPECTED_COPY_COUNT = 3;

  const WEBFORM_SUB_EXPECTED_LOAD_COUNT = 4;

  /**
   * Mock KeyValue factory.
   *
   * @var Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $mockLtsKeyValueFactory;

  /**
   * Mock EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $mockEntityTypeManager;

  /**
   * Mock Long term webform submission storage.
   *
   * @var Drupal\webform\WebformSubmissionStorageInterface
   */
  protected $mockLtsStorage;

  /**
   * Mock logger.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $mockLtsLoggerFactory;

}
