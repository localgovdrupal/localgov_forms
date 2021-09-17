<?php

namespace Drupal\Tests\bhcc_central_hub\Unit;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\bhcc_central_hub\AddressLookupService;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for the AddressLookupService class.
 *
 * @coversDefaultClass Drupal\bhcc_central_hub\AddressLookupService
 * @group bhcc
 */
class AddressLookupServiceTest extends UnitTestCase {

  /**
   * This is what we are testing.
   * @var Drupal\bhcc_central_hub\AddressLookupService
   */
  protected $test_target;

  /**
   * Store default mock api results.
   * @var Array
   */
  protected $default_lookup_results;

  /**
   * Search parameters protected property.
   * @var ReflectionProperty
   */
  protected $search_parameters_property;

  /**
   * Status code protected property.
   * @var ReflectionProperty
   */
  protected $status_code_property;

  /**
   * Results protected property.
   * @var ReflectionProperty
   */
  protected $results_property;

  /**
   * Http client.
   * @var GuzzleHttp\ClientInterface
   */
  protected $http_client;

  public function setUp() {

    // Default API results.
    $this->default_lookup_results = [
      'addresslist' => [
        [
          'display'      => '123 Fake Street  Brighton BN1 1AA',
          'name'         => '22127719',
          'flat'         => '',
          'house'        => '123',
          'street'       => 'Fake Street',
          'town'         => 'Brighton',
          'county'       => '',
          'postcode'     => 'BN1 1AA',
          'uprn'         => '000022127719',
          'ward'         => 'Regency',
          'lng'          => '-0.141638613',
          'lat'          => '50.82176903',
          'locality'     => '',
          'sao_start_no' => '',
          'pao_start_no' => '59',
          'easting'      => '530995',
          'northing'     => '104115',
          'blpu_class'   => 'RD06',
          'zoneid'       => 'Z',
          'carfree'      => '0',
          'historic'     => 'false'
        ],
      ],
    ];

    // Needs special mock as __call not supported (used by Guzzle).
    $this->http_client = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->setMethods(['post'])
      ->getMock();

    $this->test_target = new AddressLookupService($this->http_client);

    // Add protected parameters for testing as as a reflected property.
    $reflect = new \ReflectionClass(AddressLookupService::class);
    $properties = [
      'search_parameters_property' => 'searchParameters',
      'status_code_property' => 'statusCode',
      'results_property' => 'results'
    ];
    foreach($properties as $test_key => $property) {
      $this->{$test_key} = $reflect->getProperty($property);
      $this->{$test_key}->setAccessible(TRUE);
    }
  }

  /**
   * Mock a basic (null) object.
   * @param  Class $objectClass
   *   A class / interface to mock.
   * @return Object
   *   Mock object.
   */
  protected function basicMockObject($objectClass) {
    return $this->getMockBuilder($objectClass)
      ->disableOriginalConstructor()
      ->getMock();
  }

  protected function addMockApiCall($parameters, $staus_code, $body) {

    // Set up the mock http client to simulate api responses
    $response = $this->basicMockObject(ResponseInterface::class);
    $response->expects($this->once())
      ->method('getStatusCode')
      ->willReturn($staus_code);
    $response->expects($this->once())
      ->method('getBody')
      ->willReturn(json_encode($body));

    $mock_url = 'https://centralhub-accp.mendixcloud.com/rest/addresssearch/v1/lookup';
    $mock_request_options = [
      'json' => $parameters,
    ];

    $this->http_client->expects($this->once())
      ->method('post')
      ->with($mock_url, $mock_request_options)
      ->willReturn($response);

  }

  /**
   * Tests for initSearch
   *
   * We initilize the search and expect the AddressLookupService object back,
   * with the $searchParameters empty.
   */
  public function testInitSearch() {

    $returned = $this->test_target->initSearch();

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert the property $result->queryParameters is an empty array.
    $result = $this->search_parameters_property->getValue($this->test_target);
    $this->assertTrue(is_array($result));
    $this->assertEmpty($result);
  }

  /**
   * Tests for setSearchParameters
   *
   * We provide some options for the search paremters
   * and expect the AddressLookupService object back,
   * with $searchParameters populated.
   */
  public function testSetSearchParameters() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->search_parameters_property->setValue($this->test_target, []);

    $parameters_to_set = [
      'searchstring' => 'BN1 1AA',
      'offset' => 1,
      'limit' => 10,
      'addresstype' => 'residential'
    ];

    $expected = $parameters_to_set;

    $returned = $this->test_target->setSearchParameters($parameters_to_set);

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert search paremeters have been set to the expected value.
    $result = $this->search_parameters_property->getValue($this->test_target);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for setSearchParameters with nonsense
   *
   * We provide some options for the search paremters
   * and expect the AddressLookupService object back,
   * with $searchParameters left empty.
   */
  public function testSetSearchParametersWithNonsense() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->search_parameters_property->setValue($this->test_target, []);

    $parameters_to_set = [
      'garbage' => 'qwertyuiop',
      'more_garbage' => 'asdfghjkl',
      'yet_more_garbage' => 'zxcvbnm',
    ];

    $expected = [];

    $returned = $this->test_target->setSearchParameters($parameters_to_set);

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert search paremeters have been set to the expected value.
    $result = $this->search_parameters_property->getValue($this->test_target);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for cleanSearchIfPostcode
   *
   * We provide some test postcodes, and expect them to come back formatted.
   */
  public function testCleanSearchIfPostcode() {

    $test_postcodes = [
      'BN11AA',
      'bn2 2bb',
      'bn33DD',
      'Not a postcode!',
    ];

    $expected = [
      'BN1 1AA',
      'BN2 2BB',
      'BN3 3DD',
      'Not a postcode!',
    ];

    // Turn protected method into public method.
    $method = new \ReflectionMethod(AddressLookupService::class, 'cleanSearchIfPostcode');
    $method->setAccessible(TRUE);

    foreach($test_postcodes as $test) {
      $result[] = $method->invoke($this->test_target, $test);
    }

    // Assert the postcode comes out correctly formatted.
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for setSearchParameters
   *
   * We provide a search parameter with a dirty postcode
   * and expect $searchParameters populated with the formatted postcode.
   */
  public function testSetSearchParametersWithDirtyPostcode() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->search_parameters_property->setValue($this->test_target, []);

    $parametersToSet = [
      'searchstring' => 'bn11aa',
    ];

    $expected = [
      'searchstring' => 'BN1 1AA',
    ];

    $this->test_target->setSearchParameters($parametersToSet);

    // Assert search paremeters have been set to the expected value.
    $result = $this->search_parameters_property->getValue($this->test_target);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for getSearchParameters
   *
   * We call getSearchParameters
   * and expect to get the defined parameters back.
   */
  public function testGetSearchParameters() {

    $parameters = [
      'searchstring' => 'BN1 1AA',
      'offset' => 1,
      'limit' => 10,
      'addresstype' => 'residential'
    ];

    // Set the value using reflection so only testing get Search Paremeter.
    $this->search_parameters_property->setValue($this->test_target, $parameters);

    $expected = $parameters;

    $result = $this->test_target->getSearchParameters();

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for doLookup (Happy path)
   *
   * We call doLookup
   * and expect AddressLookupService object back,
   * with a status code and results populated.
   */
  public function testDoLookup() {

    $parameters = [
      'searchstring' => 'BN1 1AA',
      'addresstype' => 'residential'
    ];

    $expected_status_code = 200;

    $expected = $this->default_lookup_results;

    // Set the value using reflection so only testing get Search Paremeter.
    $this->search_parameters_property->setValue($this->test_target, $parameters);

    // Set up the mock http client to simulate api responses
    $this->addMockApiCall($parameters, 200, $this->default_lookup_results);

    $returned = $this->test_target->doLookup();

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert status code has been set to the expected value.
    $status_code = $this->status_code_property->getValue($this->test_target);
    $this->assertEquals($expected_status_code, $status_code);

    // Assert the results have the default search results.
    $result = $this->results_property->getValue($this->test_target);
    $this->assertEquals($expected, $result);

  }

  /**
   * Tests for getStatusCode
   *
   * We call getStatusCode
   * and expect to get the defined status code back.
   */
  public function testGetStatusCode() {

    $status_code = 200;

    // Set the value using reflection so only testing get method.
    $this->status_code_property->setValue($this->test_target, $status_code);

    $expected = $status_code;

    $result = $this->test_target->getStatusCode();

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for getResults
   *
   * We call getResults
   * and expect to get the defined results back.
   */
  public function testGetResults() {

    // Set the value using reflection so only testing get method.
    $this->results_property->setValue($this->test_target, $this->default_lookup_results);

    $expected = $this->default_lookup_results;

    $result = $this->test_target->getResults();

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for addressLookup (Happy path!)
   *
   * We provide address lookup details, and expect the default results back.
   */
  public function testAddressLookup() {

    // Mock the api call.
    $parameters = [
      'searchstring' => 'BN1 1AA',
      'addresstype' => 'residential'
    ];
    $this->addMockApiCall($parameters, 200, $this->default_lookup_results);

    // Create a new container object.
    $container = new ContainerBuilder();

    // Add a new instance of the AddressLookupService to the container.
    $container->set('bhcc_central_hub.address_lookup', new AddressLookupService($this->http_client));

    // Let our static method use the mock container.
    \Drupal::setContainer($container);

    $expected = $this->default_lookup_results['addresslist'];

    $result = AddressLookupService::addressLookup('BN1 1AA', 'residential');

    $this->assertEquals($expected, $result);
  }

}
