<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of PII redactor plugins.
 *
 * @see Drupal\localgov_forms\Attribute\PIIRedactor
 * @see Drupal\localgov_forms\Plugin\PIIRedactorPluginInterface
 */
class PIIRedactorPluginManager extends DefaultPluginManager implements PIIRedactorPluginManagerInterface {

  /**
   * Constructs a new PIIRedactorManager object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {

    parent::__construct('Plugin/PIIRedactor', $namespaces, $module_handler, 'Drupal\localgov_forms\Plugin\PIIRedactorPluginInterface', 'Drupal\localgov_forms\Attribute\PIIRedactor');

    $this->alterInfo('pii_redactor_info');

    $this->setCacheBackend($cache_backend, 'pii_redactor_plugins');
  }

}
