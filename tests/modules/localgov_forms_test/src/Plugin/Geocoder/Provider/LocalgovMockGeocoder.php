<?php

namespace Drupal\localgov_forms_test\Plugin\Geocoder\Provider;

use Drupal\geocoder\ProviderUsingHandlerBase;

/**
 * Declares a mock geocoder provider.
 *
 * Useful for automated testing.
 *
 * @GeocoderProvider(
 *   id = "localgov_mock_geocoder",
 *   name = "Localgov mock geocoder",
 *   handler = "\Drupal\localgov_forms_test\Geocoder\Provider\LocalgovMockGeocoder"
 * )
 */
class LocalgovMockGeocoder extends ProviderUsingHandlerBase {}
