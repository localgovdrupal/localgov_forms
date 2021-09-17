<?php

namespace Drupal\bhcc_central_hub\Plugin\Geocoder\Provider;

use Drupal\geocoder\ProviderUsingHandlerBase;

/**
 * Provides a geocoder provider using the BHCC Central Hub residential service.
 *
 * @GeocoderProvider(
 *   id = "bhcc_central_hub_residential",
 *   name = "BHCC Central Hub Residential",
 *   handler = "\Drupal\bhcc_central_hub\Geocoder\Provider\CentralHubResidential"
 * )
 */
class CentralHubResidential extends ProviderUsingHandlerBase {}
