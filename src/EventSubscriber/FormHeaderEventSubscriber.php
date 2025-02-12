<?php
namespace Drupal\localgov_forms\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\localgov_forms\Event\WebformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\block\Entity\Block;
use Drupal\webform\WebformInterface;

class FormHeaderEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  public static function getSubscribedEvents() {
    return [
      WebformEvent::EVENT_NAME => 'onCreateFormHeaderBlock',
    ];
  }


  public function onCreateFormHeaderBlock(WebformEvent $event) {
    $webform = $event->getWebform();
    $this->createFormHeaderBlock($webform);
  }

  public function createFormHeaderBlock(WebformInterface $webform) {
    $block_id = 'form_header_block';
    $block = Block::load($block_id);

    if (!$block) {
      $block = Block::create([
        'id' => $block_id,
        'theme' => \Drupal::config('system.theme')->get('default'),
        'plugin' => 'system_main_block',
        'region' => 'content',
        'settings' => [
          'label' => 'Form Header Block',
          'label_display' => 'visible',
        ],
      ]);
      $block->save();
    }

    $content = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-header-block']],
      'label' => [
        '#markup' => '<h2>' . $webform->label() . '</h2>',
      ],
      'description' => [
        '#markup' => '<p>' . $webform->getDescription() . '</p>',
      ],
      'page_title' => [
        '#markup' => '<p>' . $this->t('Current page: @title', ['@title' => \Drupal::service('title_resolver')->getTitle(\Drupal::request(), \Drupal::routeMatch()->getRouteObject())]) . '</p>',
      ],
    ];

    // $block->setContent($content);
    $block->save();
  }


}
