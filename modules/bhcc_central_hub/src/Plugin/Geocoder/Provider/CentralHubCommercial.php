<?php

namespace Drupal\bhcc_central_hub\Plugin\Geocoder\Provider;

use Drupal\geocoder\ProviderUsingHandlerBase;

/**
 * Provides a geocoder provider using the BHCC Central Hub residential service.
 *
 * @GeocoderProvider(
 *   id = "bhcc_central_hub_commercial",
 *   name = "BHCC Central Hub Commercial",
 *   handler = "\Drupal\bhcc_central_hub\Geocoder\Provider\CentralHubCommercial"
 * )
 */
class CentralHubCommercial extends ProviderUsingHandlerBase {}
