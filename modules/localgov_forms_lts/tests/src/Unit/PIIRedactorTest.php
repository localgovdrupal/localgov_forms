<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms_lts\Unit;

use Drupal\localgov_forms_lts\PIIRedactor;
use Drupal\Tests\UnitTestCase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Unit tests for PIIRedactor.
 */
class PIIRedactorTest extends UnitTestCase {

  /**
   * Tests PIIRedactorTest::findElemsToRedact().
   */
  public function testFindElemsToRedact() {

    $mock_webform = $this->createConfiguredMock(WebformInterface::class, [
      'getElementsDecodedAndFlattened' => [
        'name'          => ['#type' => 'textfield'],
        'email'         => ['#type' => 'email'],
        'subject'       => ['#type' => 'textfield'],
        'message'       => ['#type' => 'textarea'],
        'work_number'   => ['#type' => 'tel'],
        'nino'          => ['#type' => 'textfield'],
        'location'      => ['#type' => 'address'],
        'cars'          => ['#type' => 'number'],
        'gender'        => ['#type' => 'radios'],
        'ethnicity'     => ['#type' => 'checkboxes'],
        'date_of_birth' => ['#type' => 'localgov_forms_date'],
      ],
    ]);
    $mock_webform_sub = $this->createConfiguredMock(WebformSubmissionInterface::class, [
      'getWebform' => $mock_webform,
    ]);

    $elems_to_redact = PIIRedactor::findElemsToRedact($mock_webform_sub);

    $this->assertSame([
      'email',
      'work_number',
      'location',
      'cars',
      'name',
      'nino',
      'gender',
      'ethnicity',
      'date_of_birth',
    ], $elems_to_redact['full']);
    $this->assertSame(['message'], $elems_to_redact['part']);
  }

}
