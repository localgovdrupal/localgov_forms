<?php

declare (strict_types = 1);

namespace Drupal\Tests\localgov_forms\Unit;

use Drupal\localgov_forms\Geocoder\Provider\LocalgovOsPlacesGeocoder;
use Geocoder\Collection as AddressCollectionInterface;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Query\GeocodeQuery;

/**
 * Integration test for the LocalGov OS Places Geocoder.
 *
 * Uses cached query result to test functionality.  This provides a more
 * realistic test experience.
 *
 * Cached responses are saved inside the './cached-query-responses/' directory
 * the first time this test is run.  A **real API key** is necessary on the very
 * first run of this test.  This allows the test to fetch a real response from
 * the API end point and then save it within the ./cached-query-responses
 * directory.  Subsequence test runs then use the cached responses instead of
 * making real HTTP requests.
 */
class LocalgovOsPlacesIntegrationTest extends BaseTestCase {

  /**
   * Location of cached responses.
   *
   * This directory is filled in during the initial test run as long as a real
   * API key has been provided.
   */
  protected function getCacheDir() {

    return __DIR__ . '/cached-query-responses';
  }

  /**
   * Test for LocalgovOsPlacesGeocoder::geocodeQuery().
   *
   * Searches for BN1 3EJ which is a postcode.
   */
  public function testGeocodeQueryForPostcode() {

    $provider = new LocalgovOsPlacesGeocoder($this->getHttpClient(), $this->genericApiUrl, $this->postcodeApiUrl, $this->emptyApiKey);

    $result = $provider->geocodeQuery(GeocodeQuery::create('BN1 1JE'));
    $this->assertInstanceOf(AddressCollectionInterface::class, $result);

    $address = $result->first()->toArray();
    $this->assertEquals('22062038', $address['uprn']);
    $this->assertEquals(531044, $address['easting']);
  }

  /**
   * More Test for LocalgovOsPlacesGeocoder::geocodeQuery().
   *
   * Searches for "Dyke road, Brighton" which is a street address.
   */
  public function testGeocodeQueryForStreetAddress() {

    $provider = new LocalgovOsPlacesGeocoder($this->getHttpClient(), $this->genericApiUrl, $this->postcodeApiUrl, $this->emptyApiKey);

    $result = $provider->geocodeQuery(GeocodeQuery::create('Dyke road, Brighton'));
    $this->assertInstanceOf(AddressCollectionInterface::class, $result);

    $address = $result->first()->toArray();
    $this->assertEquals('22047675', $address['uprn']);
    $this->assertEquals(530759, $address['easting']);
    $this->assertEquals('7, DYKE ROAD, BRIGHTON, BN1 3FE', $address['display']);
  }

  /**
   * Street address query API endpoint.
   *
   * @var string
   */
  protected $genericApiUrl = 'https://api.os.uk/search/places/v1/find';

  /**
   * Postcode query API endpoint.
   *
   * @var string
   */
  protected $postcodeApiUrl = 'https://api.os.uk/search/places/v1/postcode';

  /**
   * API key.
   *
   * Note to developers: Fill this in with a real API key to capture cached
   * responses inside the ./cached-query-responses directory.  **Remove** the
   * API key afterwards.
   *
   * @var string
   */
  protected $emptyApiKey = '';

}
