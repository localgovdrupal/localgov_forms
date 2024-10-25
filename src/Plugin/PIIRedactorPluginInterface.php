<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Defines the required interface for all PII redactor plugins.
 */
interface PIIRedactorPluginInterface extends PluginInspectionInterface {

  /**
   * Redacts PII from given Webform submission.
   */
  public function redact(WebformSubmissionInterface $webform_submission): array;

}
