<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts\Plugin\PIIRedactor;

use Drupal\localgov_forms_lts\PIIRedactor;
use Drupal\localgov_forms\Plugin\PIIRedactorPluginInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * PII redaction plugin that acts on a best effort basis.
 *
 * phpcs:disable
 * @PIIRedactor(
 *   id          = "best_effort_pii_redactor",
 *   label       = "Best effort PII redactor",
 *   description = "Redacts Personally Identifiable Information (PII) from Webform submission on a best effort basis."
 * )
 * phpcs:enable
 *
 * @see Drupal\localgov_forms_lts\PIIRedactor
 */
class BestEffortPIIRedactor implements PIIRedactorPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function redact(WebformSubmissionInterface $webform_submission): array {

    $redacted_elements = PIIRedactor::redact($webform_submission);
    return $redacted_elements;
  }

}
