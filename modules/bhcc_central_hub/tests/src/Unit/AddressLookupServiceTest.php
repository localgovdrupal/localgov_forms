<?php

namespace Drupal\Tests\bhcc_central_hub\Unit;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\bhcc_central_hub\AddressLookupService;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\ConfigFactory;

/**
 * Unit tests for the AddressLookupService class.
 *
 * @coversDefaultClass Drupal\bhcc_central_hub\AddressLookupService
 * @group bhcc
 */
class AddressLookupServiceTest extends UnitTestCase {

  /**
   * This is what we are testing.
   *
   * @var Drupal\bhcc_central_hub\AddressLookupService
   */
  protected $testTarget;

  /**
   * Store default mock api results.
   *
   * @var array
   */
  protected $defaultLookupResults;

  /**
   * Search parameters protected property.
   *
   * @var ReflectionProperty
   */
  protected $searchParametersProperty;

  /**
   * Status code protected property.
   *
   * @var ReflectionProperty
   */
  protected $statusCodeProperty;

  /**
   * Results protected property.
   *
   * @var ReflectionProperty
   */
  protected $resultsProperty;

  /**
   * Http client.
   *
   * @var GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Config factory.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setUp() :void {

    // Default API results.
    $this->defaultLookupResults = [
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
          'historic'     => 'false',
        ],
      ],
    ];

    // Needs special mock as __call not supported (used by Guzzle).
    $this->httpClient = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->addMethods(['post'])
      ->getMock();

    $this->configFactory = $this->getMockBuilder(ConfigFactory::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory->expects($this->any())
      ->method('get')
      ->willReturn(new class {

        /**
         * Mocks Drupal\Core\Config\ConfigFactoryInterface::get().
         */
        public function get() {
          return 'https://centralhub-accp.mendixcloud.com/rest/addresssearch/v1/lookup';
        }

      });

    $this->testTarget = new AddressLookupService($this->httpClient, $this->configFactory);

    // Add protected parameters for testing as as a reflected property.
    $reflect = new \ReflectionClass(AddressLookupService::class);
    $properties = [
      'searchParametersProperty' => 'searchParameters',
      'statusCodeProperty' => 'statusCode',
      'resultsProperty' => 'results',
    ];
    foreach ($properties as $test_key => $property) {
      $this->{$test_key} = $reflect->getProperty($property);
      $this->{$test_key}->setAccessible(TRUE);
    }
  }

  /**
   * Mock a basic (null) object.
   *
   * @param string $objectClass
   *   A class / interface to mock.
   *
   * @return Object
   *   Mock object.
   */
  protected function basicMockObject(string $objectClass) {
    return $this->getMockBuilder($objectClass)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * API call wrapper.
   */
  protected function addMockApiCall($parameters, $staus_code, $body) {

    // Set up the mock http client to simulate api responses.
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

    $this->httpClient->expects($this->once())
      ->method('post')
      ->with($mock_url, $mock_request_options)
      ->willReturn($response);
  }

  /**
   * Tests for initSearch.
   *
   * We initilize the search and expect the AddressLookupService object back,
   * with the $searchParameters empty.
   */
  public function testInitSearch() {

    $returned = $this->testTarget->initSearch();

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert the property $result->queryParameters is an empty array.
    $result = $this->searchParametersProperty->getValue($this->testTarget);
    $this->assertTrue(is_array($result));
    $this->assertEmpty($result);
  }

  /**
   * Tests for setSearchParameters.
   *
   * We provide some options for the search paremters
   * and expect the AddressLookupService object back,
   * with $searchParameters populated.
   */
  public function testSetSearchParameters() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->searchParametersProperty->setValue($this->testTarget, []);

    $parameters_to_set = [
      'searchstring' => 'BN1 1AA',
      'offset' => 1,
      'limit' => 10,
      'addresstype' => 'residential',
    ];

    $expected = $parameters_to_set;

    $returned = $this->testTarget->setSearchParameters($parameters_to_set);

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert search paremeters have been set to the expected value.
    $result = $this->searchParametersProperty->getValue($this->testTarget);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for setSearchParameters with nonsense.
   *
   * We provide some options for the search paremters
   * and expect the AddressLookupService object back,
   * with $searchParameters left empty.
   */
  public function testSetSearchParametersWithNonsense() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->searchParametersProperty->setValue($this->testTarget, []);

    $parameters_to_set = [
      'garbage' => 'qwertyuiop',
      'more_garbage' => 'asdfghjkl',
      'yet_more_garbage' => 'zxcvbnm',
    ];

    $expected = [];

    $returned = $this->testTarget->setSearchParameters($parameters_to_set);

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert search paremeters have been set to the expected value.
    $result = $this->searchParametersProperty->getValue($this->testTarget);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for cleanSearchIfPostcode.
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

    foreach ($test_postcodes as $test) {
      $result[] = $method->invoke($this->testTarget, $test);
    }

    // Assert the postcode comes out correctly formatted.
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for setSearchParameters.
   *
   * We provide a search parameter with a dirty postcode
   * and expect $searchParameters populated with the formatted postcode.
   */
  public function testSetSearchParametersWithDirtyPostcode() {

    // Set the value using reflection so only testing set Search Paremeter.
    $this->searchParametersProperty->setValue($this->testTarget, []);

    $parametersToSet = [
      'searchstring' => 'bn11aa',
    ];

    $expected = [
      'searchstring' => 'BN1 1AA',
    ];

    $this->testTarget->setSearchParameters($parametersToSet);

    // Assert search paremeters have been set to the expected value.
    $result = $this->searchParametersProperty->getValue($this->testTarget);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for getSearchParameters.
   *
   * We call getSearchParameters
   * and expect to get the defined parameters back.
   */
  public function testGetSearchParameters() {

    $parameters = [
      'searchstring' => 'BN1 1AA',
      'offset' => 1,
      'limit' => 10,
      'addresstype' => 'residential',
    ];

    // Set the value using reflection so only testing get Search Paremeter.
    $this->searchParametersProperty->setValue($this->testTarget, $parameters);

    $expected = $parameters;

    $result = $this->testTarget->getSearchParameters();

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
      'addresstype' => 'residential',
    ];

    $expected_status_code = 200;

    $expected = $this->defaultLookupResults;

    // Set the value using reflection so only testing get Search Paremeter.
    $this->searchParametersProperty->setValue($this->testTarget, $parameters);

    // Set up the mock http client to simulate api responses.
    $this->addMockApiCall($parameters, 200, $this->defaultLookupResults);

    $returned = $this->testTarget->doLookup();

    // Assert this is an instance of AddressLookupService.
    $this->assertInstanceOf(AddressLookupService::class, $returned);

    // Assert status code has been set to the expected value.
    $status_code = $this->statusCodeProperty->getValue($this->testTarget);
    $this->assertEquals($expected_status_code, $status_code);

    // Assert the results have the default search results.
    $result = $this->resultsProperty->getValue($this->testTarget);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for getStatusCode.
   *
   * We call getStatusCode
   * and expect to get the defined status code back.
   */
  public function testGetStatusCode() {

    $status_code = 200;

    // Set the value using reflection so only testing get method.
    $this->statusCodeProperty->setValue($this->testTarget, $status_code);

    $expected = $status_code;

    $result = $this->testTarget->getStatusCode();

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for getResults.
   *
   * We call getResults
   * and expect to get the defined results back.
   */
  public function testGetResults() {

    // Set the value using reflection so only testing get method.
    $this->resultsProperty->setValue($this->testTarget, $this->defaultLookupResults);

    $expected = $this->defaultLookupResults;

    $result = $this->testTarget->getResults();

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
      'addresstype' => 'residential',
    ];
    $this->addMockApiCall($parameters, 200, $this->defaultLookupResults);

    // Create a new container object.
    $container = new ContainerBuilder();

    // Add a new instance of the AddressLookupService to the container.
    $container->set('bhcc_central_hub.address_lookup', new AddressLookupService($this->httpClient, $this->configFactory));

    // Let our static method use the mock container.
    \Drupal::setContainer($container);

    $expected = $this->defaultLookupResults['addresslist'];

    $result = AddressLookupService::addressLookup('BN1 1AA', 'residential');

    $this->assertEquals($expected, $result);
  }

}
