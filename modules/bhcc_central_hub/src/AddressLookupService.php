<?php

namespace Drupal\bhcc_central_hub;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * As it says on the tin.
 */
class AddressLookupService implements AddressLookupServiceInterface {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Search parameters.
   *
   * @var array
   */
  protected $searchParameters;

  /**
   * HTTP Status Code.
   *
   * @var int
   */
  protected $statusCode;

  /**
   * Results from address lookup.
   *
   * @var array
   */
  protected $results;

  /**
   * Constructs a new AddressLookupService object.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config) {
    $this->httpClient = $http_client;
    $this->config     = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function initSearch() {
    $this->searchParameters = [];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSearchParameters(Array $options) {
    foreach ($options as $key => $value) {

      switch ($key) {
        case 'searchstring':
          if (is_string($value)) {
            $this->searchParameters['searchstring'] = $this->cleanSearchIfPostcode($value);
          }
          break;

        case 'offset':
        case 'limit':
          if (is_numeric($value)) {
            $this->searchParameters[$key] = $value;
          }
          break;

        case 'addresstype':
          if ($value == 'residential' || $value == 'commercial' || $value == 'all') {
            $this->searchParameters['addresstype'] = $value;
          }
          break;

      }
    }
    return $this;
  }

  /**
   * Clean search string if a postcode.
   *
   * Sometimes postcodes can be entered in messy, eg bn11aa.
   * To find by postcode on Central hub, the postcode needs to be formatted
   * as BN1 1AA (Uppercase and space between sections).
   *
   * @param string $search_string
   *   User entered search string.
   *
   * @return string
   *   Formatted search string, if postcode,
   *   else left untransformed.
   */
  protected function cleanSearchIfPostcode(string $search_string) {
    preg_match('/^([Bb][Nn][0-4]{1,2}) ?([0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2})$/', $search_string, $matches);
    if (!empty($matches[0]) && !empty($matches[1]) && !empty($matches[2])) {
      // @todo Could use preg_replace?
      $search_string = str_replace($matches[0], strtoupper($matches[1] . ' ' . $matches[2]), $search_string);
    }
    return $search_string;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchParameters() {
    return $this->searchParameters;
  }

  /**
   * {@inheritdoc}
   */
  public function doLookup() {

    $requestOptions = [
      'json' => $this->searchParameters,
    ];

    try {
      $config = $this->config->get('bhcc_central_hub.settings');
      $service_url = $config->get('central_hub_service_url');

      $response = $this->httpClient->post($service_url, $requestOptions);
    }
    catch (RequestException $request_exception) {
      return;
    }

    $this->statusCode = $response->getStatusCode();
    $this->results = json_decode($response->getBody(), TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusCode() {
    return $this->statusCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * {@inheritdoc}
   */
  public static function addressLookup(string $search_string, string $address_type, $limit = NULL, $offset = NULL) {

    $searchParameters = [
      'searchstring' => $search_string,
      'addresstype'  => $address_type,
      'limit'        => $limit,
      'offset'       => $offset,
    ];

    $addressLookup = \Drupal::service('bhcc_central_hub.address_lookup');
    $addressLookup->initSearch()
      ->setSearchParameters($searchParameters)
      ->doLookup();

    // If the status code is not in the 200s, return to error.
    $status_code = $addressLookup->getStatusCode();
    if ($status_code < 200 || $status_code >= 300) {
      return FALSE;
    }

    $results = $addressLookup->getResults();

    // If the addresslist element is empty, return an empty array.
    if (empty($results['addresslist'])) {
      return [];
    }

    return $results['addresslist'];
  }

}
