<?php

namespace Drupal\localgov_forms\EventSubscriber;

use Drupal\localgov_forms\Event\FormHeaderDisplayEvent;
use Drupal\webform\WebformInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hide page header.
 *
 * @package Drupal\localgov_forms\EventSubscriber
 */
class FormHeaderSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FormHeaderDisplayEvent::EVENT_NAME => ['setFormHeader', 0],
    ];
  }

  /**
   * Hide page header block.
   */
  public function setFormHeader(FormHeaderDisplayEvent $event) {
    if ($event->getEntity() instanceof WebformInterface &&
      ($event->getEntity()->bundle() == 'localgov_forms_overview' ||
      $event->getEntity()->bundle() == 'localgov_forms_page')
    ) {
      $event->setVisibility(FALSE);
    }
  }

}
