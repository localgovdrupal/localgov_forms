<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms\Plugin\Geocoder\Provider;

use Drupal\geocoder\ConfigurableProviderUsingHandlerWithAdapterBase;

/**
 * Provides an Ordnance Survey Places API-based geocoder provider plugin.
 *
 * @GeocoderProvider(
 *   id        = "localgov_os_places",
 *   name      = "Localgov OS Places",
 *   handler   = "\Drupal\localgov_forms\Geocoder\Provider\LocalgovOsPlacesGeocoder",
 *   arguments = {
 *     "genericAddressQueryUrl" = "https://api.os.uk/search/places/v1/find",
 *     "postcodeQueryUrl"       = "https://api.os.uk/search/places/v1/postcode",
 *     "apiKey"                 = "",
 *     "userAgent"              = "LocalGov Drupal"
 *   }
 * )
 */
class LocalgovOsPlacesGeocoder extends configurableProviderUsingHandlerWithAdapterBase {}
