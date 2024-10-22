<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms_lts\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\localgov_forms_lts\PIIRedactorForText;

/**
 * Unit tests for PIIRedactorForText.
 */
class PIIRedactorForTextTest extends UnitTestCase {

  /**
   * Tests redaction.
   *
   * Tests redaction of emails, postcodes, and numbers from a given text.
   */
  public function testRedaction() {

    $redactable_text = "My email address is foo+bar@example.net.\n  Also reachable at qux@example.net.  My address is 7 Example road, CR8 2XX.\n  I was born on 2001-01-01.\n  I have 5 cats.";
    [, $redaction_count] = PIIRedactorForText::redact($redactable_text);

    $this->assertEquals($redaction_count, 8);

    $nonredactable_text = 'preg_replace() performs a regex search and replace.';
    [, $redaction_count] = PIIRedactorForText::redact($nonredactable_text);

    $this->assertEquals($redaction_count, 0);
  }

}
