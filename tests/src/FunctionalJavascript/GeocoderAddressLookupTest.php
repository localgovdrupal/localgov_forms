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
    $this->assertEquals('50.8208609', $latitude_hidden_field->getValue());
  }

  /**
   * Tests address caching in multiple address lookup fields.
   *
   * Tests a scenario where there are multiple address lookup elements in a form
   * but they are using different local custodian codes.  The first address
   * lookup element is restricted to Brighton and Hove; the second one is
   * restricted to Croydon.  Searching for the same search string in these two
   * elements should bring up different results.
   */
  public function testLocalCustodianCodeCaching() {

    $page           = $this->getSession()->getPage();
    $session_assert = $this->assertSession();

    $this->drupalGet('/webform/contact3');
    $session_assert->waitForElementVisible('css', '#edit-address-brighton-address-lookup-address-search-address-searchstring');

    // Lookup "sandown" in Brighton.
    $postcode_or_street_textfield_bn = $page->find('css', '#edit-address-brighton-address-lookup-address-search-address-searchstring');
    $this->assertNotEmpty($postcode_or_street_textfield_bn);
    $postcode_or_street_textfield_bn->setValue('sandown');

    $search_btn_bn = $page->find('css', '#edit-address-brighton-address-lookup-address-search-address-actions-address-searchbutton');
    $this->assertNotEmpty($search_btn_bn);

    // Click the address "Search" button.
    $search_btn_bn->click();
    $session_assert->waitForElementVisible('css', '[data-drupal-selector=edit-address-brighton-address-lookup-address-select-address-select-list]');

    $address_dropdown_bn = $page->find('css', '[data-drupal-selector=edit-address-brighton-address-lookup-address-select-address-select-list]');
    $this->assertNotEmpty($address_dropdown_bn);

    // Select the one and only address option from the address dropdown.
    $address_dropdown_bn->selectOption('22087484');
    $session_assert->waitForElementVisible('css', '#edit-address-brighton-address-1');

    // Verify if the town and postcode fields have been filled in correctly.
    $town_textfield_bn = $page->find('css', '#edit-address-brighton-town-city');
    $this->assertNotEmpty($town_textfield_bn);
    $postcode_textfield_bn = $page->find('css', '#edit-address-brighton-postcode');
    $this->assertNotEmpty($postcode_textfield_bn);

    $this->assertEquals('BRIGHTON', $town_textfield_bn->getValue());
    $this->assertEquals('BN2 3EJ', $postcode_textfield_bn->getValue());

    // Now lookup "sandown" again, but in Croydon.
    $postcode_or_street_textfield_cr = $page->find('css', '#edit-address-croydon-address-lookup-address-search-address-searchstring');
    $this->assertNotEmpty($postcode_or_street_textfield_cr);
    $postcode_or_street_textfield_cr->setValue('sandown');

    $search_btn_cr = $page->find('css', '#edit-address-croydon-address-lookup-address-search-address-actions-address-searchbutton');
    $this->assertNotEmpty($search_btn_cr);

    // Click the address "Search" button.
    $search_btn_cr->click();
    $session_assert->waitForElementVisible('css', '[data-drupal-selector=edit-address-croydon-address-lookup-address-select-address-select-list]');

    $address_dropdown_cr = $page->find('css', '[data-drupal-selector=edit-address-croydon-address-lookup-address-select-address-select-list]');
    $this->assertNotEmpty($address_dropdown_cr);

    // Select the one and only address option from the address dropdown.
    $address_dropdown_cr->selectOption('100020656118');
    $session_assert->waitForElementVisible('css', '#edit-address-croydon-address-1');

    // Verify if the town and postcode fields have been filled in correctly.
    $town_textfield_cr = $page->find('css', '#edit-address-croydon-town-city');
    $this->assertNotEmpty($town_textfield_cr);
    $postcode_textfield_cr = $page->find('css', '#edit-address-croydon-postcode');
    $this->assertNotEmpty($postcode_textfield_cr);

    // The filled in values should be different from Brighton's because this
    // address lookup field is restricted to Croydon.
    $this->assertEquals('LONDON', $town_textfield_cr->getValue());
    $this->assertEquals('SE25 4XE', $postcode_textfield_cr->getValue());
  }

}
