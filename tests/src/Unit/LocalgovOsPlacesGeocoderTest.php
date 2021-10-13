<?php

declare(strict_types = 1);

namespace Drupal\Tests\localgov_forms\Unit;

use Drupal\localgov_forms\Geocoder\Provider\LocalgovOsPlacesGeocoder;
use Drupal\Tests\UnitTestCase;
use Geocoder\Collection as AddressCollectionInterface;
use Geocoder\Query\GeocodeQuery;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Unit tests for the LocalgovOsPlacesGeocoder class.
 */
class LocalgovOsPlacesGeocoderTest extends UnitTestCase {

  /**
   * Tests for LocalgovOsPlacesGeocoder::geocodeQuery().
   */
  public function testGeocodeQuery() {

    $mock_generic_query_url  = 'https://api.example.net/search/places/v1/find';
    $mock_postcode_query_url = 'https://api.example.net/search/places/v1/postcode';
    $mock_api_key            = 'I am a mock API key.';

    $mock_http_response = $this->createMock(ResponseInterface::class);
    $mock_http_response->expects($this->any())->method('getStatusCode')->willReturn(200);
    $mock_http_response->expects($this->any())->method('getBody')->willReturn(json_encode([
      'header'  => [],
      'results' => [
        [
          'DPA' => [
            "UPRN" => "22062038",
            "UDPRN" => "2183079",
            "ADDRESS" => "BRIGHTON & HOVE CITY COUNCIL, BARTHOLOMEW HOUSE, BARTHOLOMEW SQUARE, BRIGHTON, BN1 1JE",
            "ORGANISATION_NAME" => "BRIGHTON & HOVE CITY COUNCIL",
            "BUILDING_NAME" => "BARTHOLOMEW HOUSE",
            "THOROUGHFARE_NAME" => "BARTHOLOMEW SQUARE",
            "POST_TOWN" => "BRIGHTON",
            "POSTCODE" => "BN1 1JE",
            "RPC" => "2",
            "X_COORDINATE" => 531044.0,
            "Y_COORDINATE" => 104015.0,
            "STATUS" => "APPROVED",
            "LOGICAL_STATUS_CODE" => "1",
            "CLASSIFICATION_CODE" => "P",
            "CLASSIFICATION_CODE_DESCRIPTION" => "Parent Shell",
            "LOCAL_CUSTODIAN_CODE" => 1445,
            "LOCAL_CUSTODIAN_CODE_DESCRIPTION" => "BRIGHTON & HOVE",
            "POSTAL_ADDRESS_CODE" => "D",
            "POSTAL_ADDRESS_CODE_DESCRIPTION" => "A record which is linked to PAF",
            "BLPU_STATE_CODE" => NULL,
            "BLPU_STATE_CODE_DESCRIPTION" => "Unknown/Not applicable",
            "TOPOGRAPHY_LAYER_TOID" => "osgb5000005183216046",
            "LAST_UPDATE_DATE" => "12/11/2018",
            "ENTRY_DATE" => "05/07/2004",
            "LANGUAGE" => "EN",
            "MATCH" => 1.0,
            "MATCH_DESCRIPTION" => "EXACT",
          ],
        ],
      ],
    ]));

    $mock_http_client = $this->createMock(HttpClient::class);
    $mock_http_client->expects($this->any())->method('sendRequest')->willReturn($mock_http_response);

    $test_obj = new LocalgovOsPlacesGeocoder($mock_http_client, $mock_generic_query_url, $mock_postcode_query_url, $mock_api_key);

    $mock_postcode_query = GeocodeQuery::create('BN1 1JE');
    $result = $test_obj->geocodeQuery($mock_postcode_query);

    $this->assertInstanceOf(AddressCollectionInterface::class, $result);
    $address = $result->first()->toArray();
    $this->assertEquals('22062038', $address['uprn']);
    $this->assertEquals(531044, $address['easting']);
  }

}
