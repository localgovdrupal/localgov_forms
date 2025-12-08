<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * React to config changes.
 */
final class ConfigEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a ConfigEventSubscriber object.
   */
  public function __construct(
    private readonly ElementInfoManagerInterface $pluginManagerElementInfo,
  ) {}

  /**
   * Config saved.
   *
   * Clear element info cache if mark optional configuration changed.
   *
   * @see \Drupal\localgov_forms\Hook\ThemeHooks
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    if (($config = $event->getConfig()) &&
      ($config->getName() == 'webform.settings') &&
      $event->isChanged('third_party_settings.localgov_forms.mark_optional')
    ) {
      $this->pluginManagerElementInfo->clearCachedDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
    ];
  }

}
