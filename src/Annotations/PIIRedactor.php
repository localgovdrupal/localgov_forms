<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for PIIRedactor plugins.
 *
 * These plugins redact Personally Identifiable Information (PII) from Webform
 * submissions.
 */
class PIIRedactor extends Plugin {

  /**
   * As it says on the tin.
   *
   * @var string
   */
  public $id;

  /**
   * See above.
   *
   * @var string
   */
  public $label;

  /**
   * See above again.
   *
   * @var string
   */
  public $description = '';

}
