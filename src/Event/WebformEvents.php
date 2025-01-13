<?php

namespace Drupal\localgov_forms\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\webform\WebformInterface;

/**
 * Defines events for webform creation in localgov_forms.
 */
class WebformEvents extends Event {

  const EVENT_NAME = 'localgov_forms.webform_created';

  /**
   * The newly created webform.
   *
   * @var \Drupal\webform\WebformInterface
   */
  protected $webform;

  /**
   * Constructs a WebformEvents object.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   The newly created webform.
   */
  public function __construct(WebformInterface $webform) {
    $this->webform = $webform;
  }

  /**
   * Gets the newly created webform.
   *
   * @return \Drupal\webform\WebformInterface
   *   The newly created webform.
   */
  public function getWebform(): WebformInterface {
    return $this->webform;
  }
}
