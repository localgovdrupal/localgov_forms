<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Sets up and tests a Geocoder-based address lookup plugin.
 */
class GeocoderAddressLookupTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['localgov_forms', 'localgov_forms_test'];

  /**
   * As it says on the tin.
   *
   * @var array
   */
  protected $defaultTheme = 'stark';

  /**
   * Test for postcode-based address lookup.
   */
  public function testAddressLookup() {

    $page           = $this->getSession()->getPage();
    $session_assert = $this->assertSession();

    // Load our webform.
    $this->drupalGet('/webform/contact2');
    $session_assert->waitForElementVisible('css', '#edit-address-address-lookup-address-search-address-searchstring');

    // Fill in the postcode.
    $postcode_or_street_textfield = $page->find('css', '#edit-address-address-lookup-address-search-address-searchstring');
    $this->assertNotEmpty($postcode_or_street_textfield);
    $postcode_or_street_textfield->setValue('BN1 1JE');

    $search_btn = $page->find('css', '#edit-address-address-lookup-address-search-address-actions-address-searchbutton');
    $this->assertNotEmpty($search_btn);

    // Click the address "Search" button.
    $search_btn->click();
    $session_assert->waitForElementVisible('css', '[data-drupal-selector=edit-address-address-lookup-address-select-address-select-list]');

    $address_dropdown = $page->find('css', '[data-drupal-selector=edit-address-address-lookup-address-select-address-select-list]');
    $this->assertNotEmpty($address_dropdown);

    // Select the one and only address option from the address dropdown.
    $address_dropdown->selectOption('000022062038');
    $session_assert->waitForElementVisible('css', '#edit-address-address-1');

    // Verify if the address fields have been filled in correctly.
    $address1_textfield = $page->find('css', '#edit-address-address-1');
    $this->assertNotEmpty($address1_textfield);
    $address2_textfield = $page->find('css', '#edit-address-address-2');
    $this->assertNotEmpty($address2_textfield);
    $town_textfield = $page->find('css', '#edit-address-town-city');
    $this->assertNotEmpty($town_textfield);
    $postcode_textfield = $page->find('css', '#edit-address-postcode');
    $this->assertNotEmpty($postcode_textfield);
    $uprn_hidden_field = $page->find('css', '[data-drupal-selector="edit-address-uprn"]');
    $this->assertNotEmpty($uprn_hidden_field);
    $latitude_hidden_field = $page->find('css', '[data-drupal-selector="edit-address-lat"]');
    $this->assertNotEmpty($latitude_hidden_field);

    $this->assertEquals('Brighton & Hove City Council, Bartholomew House', $address1_textfield->getValue());
    $this->assertEquals('Bartholomew Square', $address2_textfield->getValue());
    $this->assertEquals('Brighton', $town_textfield->getValue());
    $this->assertEquals('BN1 1JE', $postcode_textfield->getValue());
    $this->assertEquals('000022062038', $uprn_hidden_field->getValue());
    $this->assertEquals('-0.140979', $latitude_hidden_field->getValue());
  }

}
