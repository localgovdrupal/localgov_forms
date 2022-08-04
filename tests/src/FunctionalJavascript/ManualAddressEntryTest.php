<?php

declare(strict_types = 1);

namespace Drupal\Tests\localgov_forms\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests for the "Can't find the address?" button.
 */
class ManualAddressEntryTest extends WebDriverTestBase {

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
   * Tests the Manual address entry button.
   *
   * - In the "contact2" Webform, the manual address entry button titled
   *   "Can't find the address?" should be visible from get go.
   * - But in the "contact4" Webform, this button should only reveal itself
   *   after an address search.
   */
  public function testManualAddressEntryBtnPresence() {

    $page           = $this->getSession()->getPage();
    $session_assert = $this->assertSession();

    // Load the *contact2* webform.
    $this->drupalGet('/webform/contact2');
    $session_assert->waitForElementVisible('css', '#edit-address-address-lookup-address-search-address-searchstring');

    // The "Can't see the address?" button should be visible at this stage.
    $manual_address_entry_btn = $page->find('css', '.js-manual-address');
    $this->assertNotEmpty($manual_address_entry_btn);
    $this->assertTrue($manual_address_entry_btn->isVisible());

    // Now load the *contact4* webform where the "Can't find the address?"
    // button appears after an address search.
    $this->drupalGet('/webform/contact4');
    $session_assert->waitForElementVisible('css', '#edit-address-address-lookup-address-search-address-searchstring');

    // The "Can't see the address?" button should *not* be visible at this time.
    $manual_address_entry_btn = $page->find('css', '.js-manual-address');
    $this->assertNotEmpty($manual_address_entry_btn);
    $this->assertFalse($manual_address_entry_btn->isVisible());

    // Fill in the postcode and search.
    $postcode_or_street_textfield = $page->find('css', '#edit-address-address-lookup-address-search-address-searchstring');
    $this->assertNotEmpty($postcode_or_street_textfield);
    $postcode_or_street_textfield->setValue('BN1 1JE');

    $search_btn = $page->find('css', '#edit-address-address-lookup-address-search-address-actions-address-searchbutton');
    $this->assertNotEmpty($search_btn);

    $search_btn->click();
    $session_assert->waitForElementVisible('css', '[data-drupal-selector=edit-address-address-lookup-address-select-address-select-list]');

    // The "Can't see the address?" button should appear at this stage.
    $this->assertTrue($manual_address_entry_btn->isVisible());

    // At this point, the address entry fields are all hidden.
    $address1_textfield = $page->find('css', '#edit-address-address-1');
    $this->assertNotEmpty($address1_textfield);
    $this->assertFalse($address1_textfield->isVisible());
    $address2_textfield = $page->find('css', '#edit-address-address-2');
    $this->assertNotEmpty($address2_textfield);
    $this->assertFalse($address2_textfield->isVisible());
    $town_textfield = $page->find('css', '#edit-address-town-city');
    $this->assertNotEmpty($town_textfield);
    $this->assertFalse($town_textfield->isVisible());
    $postcode_textfield = $page->find('css', '#edit-address-postcode');
    $this->assertNotEmpty($postcode_textfield);
    $this->assertFalse($postcode_textfield->isVisible());

    // But clicking the "Can't see the address?" button should reveal the
    // address entry fields.
    $manual_address_entry_btn->click();

    $this->assertTrue($address1_textfield->isVisible());
    $this->assertTrue($address2_textfield->isVisible());
    $this->assertTrue($town_textfield->isVisible());
    $this->assertTrue($postcode_textfield->isVisible());
  }

}
