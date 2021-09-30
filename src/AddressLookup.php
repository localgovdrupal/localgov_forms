<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms;

use Geocoder\Location;

/**
 * Address lookup service.
 *
 * Implements Geocoder-based address lookup.
 */
class AddressLookup {

  /**
   * Searches addresses using the given Geocoding plugins.
   */
  public function search(array $search_param, array $geocoder_plugin_ids) :array {

    $search_str = self::toSearchStr($search_param);
    $geocoder_plugins = $this->geocoderSelector->getSelectedPlugins($geocoder_plugin_ids);

    $addr_list = $this->geocoder->geocode($search_str, $geocoder_plugins);
    $formatted_addr_list = array_map('self::reformat', iterator_to_array($addr_list));

    return $formatted_addr_list;
  }

  /**
   * Turns search parameters into a search string.
   *
   * This search string is suitable for geocoding.
   *
   * @see Drupal\geocoder_address\AddressService::addressArrayToGeoString()
   */
  public static function toSearchStr(array $param) :string {

    $str = implode(' ', $param);
    return $str;
  }

  /**
   * Prepares address array.
   *
   * This array is suitable for the Address lookup widget.
   */
  public static function reformat(Location $addr) :array {

    return [
      'street'   => sprintf("%s %s", $addr->getStreetNumber(), $addr->getStreetName()),
      'flat'     => '',
      'house'    => '',
      'uprn'     => '',
      'town'     => $addr->getLocality(),
      'postcode' => $addr->getPostalCode(),
      'src'      => $addr->getProvidedBy(),
      'lat'      => $addr->getCoordinates()->getLatitude(),
      'lng'      => $addr->getCoordinates()->getLongitude(),
      'country'  => $addr->getCountry()->getName(),
      'country_code' => $addr->getCountry()->getCode(),
    ];
  }

  /**
   * Keeps track of Geocoding related services.
   */
  public function __construct($geocoder, $geocoder_selector) {

    $this->geocoder = $geocoder;
    $this->geocoderSelector = $geocoder_selector;
  }

  /**
   * Geocoder service.
   *
   * @var Drupal\geocoder\Geocoder
   */
  protected $geocoder;

  /**
   * Provides Geocoder plugin instances.
   *
   * @var Drupal\localgov_forms\Geocoders
   */
  protected $geocoderSelector;

}
