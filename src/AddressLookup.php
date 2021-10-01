<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms;

use Geocoder\Location;
use Drupal\localgov_forms\Geocoder\Model\LocalgovAddressInterface;

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
    if (is_null($addr_list) || $addr_list === FALSE) {
      return [];
    }

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
   *
   * UPRN is used as the unique id for each address record.  For addresses
   * without the UPRN property, we use "(lat,lon)" as the unique id.
   */
  public static function reformat(Location $addr) :array {

    $unique_id     = '';
    $display_name  = '';
    $uprn          = '';
    $flat          = '';
    $street_number = $addr->getStreetNumber();
    $street_name   = $addr->getStreetName();
    $locality      = $addr->getLocality();
    $postcode      = $addr->getPostalCode();
    $country_name  = $addr->getCountry()->getName();
    $country_code  = $addr->getCountry()->getCode();
    $latitude      = $addr->getCoordinates()->getLatitude();
    $longitude     = $addr->getCoordinates()->getLongitude();

    $is_lgd_address = $addr instanceof LocalgovAddressInterface;
    if ($is_lgd_address) {
      $uprn         = $addr->getUprn();
      $unique_id    = $uprn;
      $display_name = $addr->getDisplayName();
      $flat         = $addr->getFlat();
    }
    else {
      $unique_id = sprintf("(%s,%s)", $latitude, $longitude);

      $display_name_parts = [
        $street_number, $street_name, $locality, $postcode, $country_name,
      ];
      $display_name = implode(', ', array_filter($display_name_parts));
    }

    return [
      'name'         => $unique_id,
      'uprn'         => $uprn,
      'display'      => $display_name,
      'street'       => $street_name,
      'flat'         => $flat,
      'house'        => $street_number,
      'town'         => $locality,
      'postcode'     => $postcode,
      'src'          => $addr->getProvidedBy(),
      'lat'          => $latitude,
      'lng'          => $longitude,
      'country'      => $country_name,
      'country_code' => $country_code,
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
