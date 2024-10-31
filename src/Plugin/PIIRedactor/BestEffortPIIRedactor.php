<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Plugin\PIIRedactor;

use Drupal\localgov_forms\Attribute\PIIRedactor;
use Drupal\localgov_forms\BestEffortPIIRedactor as BestEffortRedactor;
use Drupal\localgov_forms\Plugin\PIIRedactorPluginBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * PII redaction plugin that acts on a best effort basis.
 *
 * @see Drupal\localgov_forms\PIIRedactor
 */
#[PIIRedactor(
  id          : 'best_effort_pii_redactor',
  label       : 'Best effort PII redactor',
  description : 'Redacts Personally Identifiable Information (PII) from a Webform submission on a best effort basis.',
 )]
class BestEffortPIIRedactor extends PIIRedactorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function redact(WebformSubmissionInterface $webform_submission): array {

    $redacted_elements = BestEffortRedactor::redact($webform_submission);
    return $redacted_elements;
  }

}
