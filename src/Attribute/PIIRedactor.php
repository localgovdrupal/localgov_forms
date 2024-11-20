<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines a PIIRedactor attribute for plugin discovery.
 *
 * PIIRedactor plugins redact Personally Identifiable Information (PII) from
 * Webform Submissions.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PIIRedactor extends Plugin {

  /**
   * Wires dependencies.
   */
  public function __construct(
    public readonly string $id,
    public readonly string $label,
    public readonly ?string $description = NULL,
  ) {}

}
