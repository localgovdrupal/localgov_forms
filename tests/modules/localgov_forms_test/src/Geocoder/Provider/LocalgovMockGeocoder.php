<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_test\Geocoder\Provider;

use Geocoder\Collection as LocationCollectionInterface;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider as ProviderInterface;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use LocalgovDrupal\OsPlacesGeocoder\Model\OsPlacesAddress;

/**
 * A Mock PHP Geocoder provider.
 *
 * Generates a collection of UprnAddress instances for automated testing
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
    $is_sandown_rd = !strcasecmp($search_string, 'sandown');

    $local_custodian_code     = $query->getData('local_custodian_code');
    $is_restricted_to_bhcc    = ($local_custodian_code === self::BHCC_LOCAL_CUSTODIAN_CODE);
    $is_restricted_to_croydon = ($local_custodian_code === self::CROYDON_LOCAL_CUSTODIAN_CODE);

    if ($is_bhcc_hq) {
      $results[] = OsPlacesAddress::createFromArray([
        'providedBy'       => $this->getName(),
        'org'              => 'Brighton & Hove City Council',
        'houseName'        => 'Bartholomew House',
        'streetNumber'     => NULL,
        'streetName'       => 'Bartholomew Square',
        'flat'             => '',
        'locality'         => 'Brighton',
        'postalCode'       => 'BN1 1JE',
        'country'          => 'United Kingdom',
        'countryCode'      => 'GB',
        'display'          => 'Brighton & Hove City Council, Bartholomew House, Bartholomew Square, Brighton, BN1 1JE',
        'latitude'         => '50.8208609',
        'longitude'        => '-0.1409790',
        'easting'          => '531044',
        'northing'         => '104015',
        'uprn'             => '000022062038',
      ]);
    }
    elseif ($is_sandown_rd && $is_restricted_to_bhcc) {
      $results[] = OsPlacesAddress::createFromArray([
        'providedBy'       => $this->getName(),
        'org'              => '',
        'houseName'        => '',
        'streetNumber'     => '2',
        'streetName'       => 'SANDOWN ROAD',
        'flat'             => '',
        'locality'         => 'BRIGHTON',
        'postalCode'       => 'BN2 3EJ',
        'country'          => 'United Kingdom',
        'countryCode'      => 'GB',
        'display'          => '2, SANDOWN ROAD, BRIGHTON, BN2 3EJ',
        'latitude'         => '50.8317948',
        'longitude'        => '-0.1177381',
        'easting'          => '532648',
        'northing'         => '105273',
        'uprn'             => '22087484',
      ]);
    }
    elseif ($is_sandown_rd && $is_restricted_to_croydon) {
      $results[] = OsPlacesAddress::createFromArray([
        'providedBy'       => $this->getName(),
        'org'              => '',
        'houseName'        => '',
        'streetNumber'     => '4',
        'streetName'       => 'SANDOWN ROAD',
        'flat'             => '',
        'locality'         => 'LONDON',
        'postalCode'       => 'SE25 4XE',
        'country'          => 'United Kingdom',
        'countryCode'      => 'GB',
        'display'          => '4, SANDOWN ROAD, LONDON, SE25 4XE',
        'latitude'         => '51.3935247',
        'longitude'        => '-0.0662865',
        'easting'          => '534630',
        'northing'         => '167828',
        'uprn'             => '100020656118',
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

  const BHCC_LOCAL_CUSTODIAN_CODE = 1445;
  const CROYDON_LOCAL_CUSTODIAN_CODE = 5240;

}
