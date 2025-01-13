<?php

namespace Drupal\localgov_forms\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Event\WebformSubmissionFormEvent;
use Drupal\localgov_forms\Event\WebformEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormHeaderEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      WebformEvents::EVENT_NAME => ['onFormAlter', 0],
    ];
  }

  /**
   * Alters the webform to add header information.
   *
   * @param \Drupal\localgov_forms\Event\WebformEvents $event
   *   The webform submission form event.
   */
  public function onFormAlter(WebformEvents $event) {
    $newly_created_webform = $event->getWebform();

    $header = [
      '#type' => 'container',
      '#weight' => -100,
      'label' => [
        '#markup' => '<h1>' . $newly_created_webform->label() . '</h1>',
      ],
      'description' => [
        '#markup' => '<p>' . $newly_created_webform->getDescription() . '</p>',
      ],
      'page_title' => [
        '#markup' => '<p>' . $this->t('Current page: @title', ['@title' => \Drupal::service('title_resolver')->getTitle(\Drupal::request(), \Drupal::routeMatch()->getRouteObject())]) . '</p>',
      ],
    ];

    $form['header'] = $header;
  }
}