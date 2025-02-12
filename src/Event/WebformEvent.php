<?php

namespace Drupal\localgov_forms\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\webform\WebformInterface;
use Drupal\block\Entity\Block;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines events for webform creation in localgov_forms.
 */
class WebformEvent extends Event {
  use StringTranslationTrait;

  const EVENT_NAME = 'localgov_forms.createAndRenderHeaderBlock';

  /**
   * The newly created webform.
   *
   * @var \Drupal\webform\WebformInterface
   */
  protected $webform;

  public function __construct(WebformInterface $webform) {
    $this->webform = $webform;
  }

  public function getWebform(): WebformInterface {
    return $this->webform;
  }

}