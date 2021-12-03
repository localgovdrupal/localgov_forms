<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms_test\Geocoder\Provider;

use Geocoder\Collection as LocationCollectionInterface;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider as ProviderInterface;
use Drupal\localgov_forms\Geocoder\Model\LocalgovAddress;

/**
 * A Mock PHP Geocoder provider.
 *
 * Generates a collection of LocalgovAddress instances for automated testing
 * purposes.
 */
class LocalgovMockGeocoder implements ProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() :string {

    return 'localgov_mock_geocoder';
  }

  /**
   * Our mock address generator.
   *
   * {@inheritdoc}
   */
  public function geocodeQuery(GeocodeQuery $query) :LocationCollectionInterface {

    $results       = [];
    $search_string = $query->getText();
    $is_bhcc_hq    = !strcasecmp($search_string, 'BN1 1JE');

    if ($is_bhcc_hq) {
      $results[] = LocalgovAddress::createFromArray([
        'providedBy'       => $this->getName(),
        'streetNumber'     => 'Bartholomew House',
        'streetName'       => 'Bartholomew Square',
        'flat'             => '',
        'locality'         => 'Brighton',
        'postalCode'       => 'BN1 1JE',
        'county'           => 'East Sussex',
        'country'          => 'United Kingdom',
        'countryCode'      => 'GB',
        'display'          => 'Bartholomew House, Bartholomew Square, Brighton, BN1 1JE',
        'formattedAddress' => 'Bartholomew House, Bartholomew Square, Brighton, BN1 1JE',
        'latitude'         => '-0.1409790',
        'longitude'        => '50.8208609',
        'easting'          => '531044',
        'northing'         => '104015',
        'uprn'             => '000022062038',
      ]);
    }

    return new AddressCollection($results);
  }

  /**
   * {@inheritdoc}
   */
  public function reverseQuery(ReverseQuery $query) :LocationCollectionInterface {

    throw new UnsupportedOperation('Reverse geocoding is unavailable in the LocalGov mock geocoder provider.');
  }

}
