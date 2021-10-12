<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manages Geocoder plugins relevant to a Webform element.
 *
 * Different Webform elements can have different selection of Geocoder plugins.
 * Here we fetch the right plugin instances for a Webform element.
 */
class Geocoders {

  /**
   * Prepares Geocoder plugin instances.
   *
   * Checks the given plugin id list for installed plugins.  Then returns
   * instances of those installed plugins.
   */
  public function getSelectedPlugins(array $selected_plugin_ids) :array {
    $selected_n_available_plugin_ids = $this->listSelectedAndAvailablePluginIds($selected_plugin_ids);

    $selected_n_available_plugin_instances = $this->geocoderProviderStorage->loadMultiple($selected_n_available_plugin_ids);
    return $selected_n_available_plugin_instances;
  }

  /**
   * List labels of installed Geocoder plugins.
   */
  public function listInstalledPluginNames() :array {

    $installed_geocoders = $this->geocoderProviderStorage->loadMultiple();

    $geocoder_names = array_map(fn($geocoder) => $geocoder->label(), $installed_geocoders);
    return $geocoder_names;
  }

  /**
   * Lists Ids of installed Geocoder plugins.
   */
  public function listInstalledPluginIds() :array {

    $installed_geocoders = $this->geocoderProviderStorage->loadMultiple();

    $geocoder_ids = array_map(fn($geocoder) => $geocoder->id(), $installed_geocoders);
    return $geocoder_ids;
  }

  /**
   * Filters the given plugin id list for installed Geocoder plugins.
   */
  public function listSelectedAndAvailablePluginIds(array $selected_plugin_ids) :array {

    $installed_geocoder_ids = $this->listInstalledPluginIds();
    $selected_n_available_plugin_ids = array_intersect($installed_geocoder_ids, $selected_plugin_ids);

    return $selected_n_available_plugin_ids;
  }

  /**
   * Keeps track of the geocoder_provider entity storage.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {

    $this->geocoderProviderStorage = $entity_type_manager->getStorage('geocoder_provider');
  }

  /**
   * Entity storage for geocoder_provider entity.
   *
   * @var Drupal\Core\Entity\EntityStorageInterface
   */
  protected $geocoderProviderStorage;

}
