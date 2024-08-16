<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts;

/**
 * Redacts from given text.
 *
 * Redacts Personally Identifiable Information (PII) from a given chunk of text.
 * Redacted items:
 * - UK postcode.
 * - Email address.
 * - Numbers.
 *
 * Email address redaction works for commonly seen formats only.  So
 * foo@example.net will be fully redacted, but foo\@bar@example will only be
 * partially redacted.
 */
class PIIRedactorForText {

  /**
   * Redacts email, postcode, number.
   */
  public static function redact(string $text): array {

    [$w_redacted_postcodes, $count_postcode] = self::redactPostcodes($text);
    [$w_redacted_postcode_n_emails, $count_email] = self::redactEmails($w_redacted_postcodes);
    [$redacted_final, $count_num] = self::redactNumbers($w_redacted_postcode_n_emails);

    $redaction_final_count = $count_postcode + $count_email + $count_num;

    return [$redacted_final, $redaction_final_count];
  }

  /**
   * Redacts postcodes.
   */
  public static function redactPostcodes(string $text): array {

    $postcode_regex = sprintf("#%s#i", self::UK_SIMPLE_POSTCODE_REGEX);
    $replacement_count = 0;
    $redacted = preg_replace($postcode_regex, self::REDACTED_POSTCODE_LABEL, $text, count: $replacement_count);

    return [$redacted, $replacement_count];
  }

  /**
   * Redacts email addresses.
   */
  public static function redactEmails(string $text): array {

    $email_regex = sprintf("#%s#i", self::SIMPLE_EMAIL_REGEX);
    $replacement_count = 0;
    $redacted = preg_replace($email_regex, self::REDACTED_EMAIL_LABEL, $text, count: $replacement_count);

    return [$redacted, $replacement_count];
  }

  /**
   * Redacts numbers.
   */
  public static function redactNumbers(string $text): array {

    $replacement_count = 0;
    $redacted = preg_replace('#\d+#', self::REDACTED_NUMBER_LABEL, $text, count: $replacement_count);

    return [$redacted, $replacement_count];
  }

  /**
   * Regex for spotting *most* UK postcodes.
   *
   * @see https://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom#Validation
   */
  const UK_SIMPLE_POSTCODE_REGEX = '[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}';

  /**
   * Regex for spotting *most* email addresses.
   *
   * The email address format is too complex to match with any regex.  This
   * regex catches most simple ones.
   *
   * @see https://www.linuxjournal.com/article/9585
   */
  const SIMPLE_EMAIL_REGEX = '[a-zA-Z0-9_+.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+';

  const REDACTED_POSTCODE_LABEL = 'REDACTED_POSTCODE';
  const REDACTED_EMAIL_LABEL    = 'REDACTED_EMAIL';
  const REDACTED_NUMBER_LABEL   = 'REDACTED_NUMBER';

}
