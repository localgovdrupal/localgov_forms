<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests error handling of the address lookup element.
 *
 * The address lookup element should only raise errors when one of its
 * subelements are required.
 */
class AddressLookupErrorMsgTest extends WebDriverTestBase {

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
   * Tests address lookup element's ability to remain inactive.
   *
   * - Submits an empty form with no required elements.
   */
  public function testEmptyFormSubmission(): void {

    $session_assert = $this->assertSession();

    $this->drupalGet('/webform/address_error_message_test_form1');
    $this->submitForm(edit: [], submit: 'Submit');
    $session_assert->waitForElementVisible('css', '.webform-confirmation__message');

    $session_assert->pageTextContains('Thank you, your form has been successfully submitted.');
  }

  /**
   * Address lookup element should not tamper with errors raised by others.
   *
   * - Submits a form with a required radio button.  The address lookup element
   *   should not raise any errors.
   */
  public function testRequiredRadio(): void {

    $session_assert = $this->assertSession();

    // First, try submitting an empty form.
    $this->drupalGet('/webform/address_error_message_test_form2');
    $this->submitForm(edit: [], submit: 'Submit');

    $session_assert->statusMessageContains('Does it have an address?', type: 'error');

    // Next, select the required radio and submit again.
    $this->submitForm(edit: ['does_it_have_an_address' => 'Yes'], submit: 'Submit');
    $session_assert->waitForElementVisible('css', '.webform-confirmation__message');

    $session_assert->pageTextContains('Thank you, your form has been successfully submitted.');
  }

  /**
   * Address lookup element should raise error if a required subfield is empty.
   *
   * - Submits an empty form.  Should bring up an error messege from the radio
   *   element.
   * - Selects a radio that does not bring up the address lookup element.  Then
   *   submits form.  Should not bring up any error messages because address
   *   lookup element is hidden.
   * - Selects a radio that brings up the address lookup form.  Then submits
   *   form without selecting or entering any address.  Should result in error
   *   messages as the postcode subelement is required but empty.
   * - Selects radio and fills in postcode.  Form submission should succeed.
   */
  public function testRequiredRadioAndPostcode(): void {

    $session_assert = $this->assertSession();

    // Submit an empty form.
    $this->drupalGet('/webform/address_error_message_test_form3');
    $this->submitForm(edit: [], submit: 'Submit');

    $session_assert->statusMessageContains('Does it have an address?', type: 'error');

    // Submit after selecting a radio button that does not bring up the address
    // lookup element.
    $this->submitForm(edit: ['does_it_have_an_address' => 'No'], submit: 'Submit');
    $session_assert->waitForElementVisible('css', '.webform-confirmation__message');

    $session_assert->pageTextContains('Thank you, your form has been successfully submitted.');

    // Submit after selecting a radio button that brings up the address lookup
    // element.  But don't fill in the required postcode.
    $this->drupalGet('/webform/address_error_message_test_form3');
    $this->submitForm(edit: ['does_it_have_an_address' => 'Yes'], submit: 'Submit');
    $session_assert->waitForElementVisible('css', '.messages--error');

    $session_assert->statusMessageContains('2 errors have been found:', type: 'error');
    $session_assert->statusMessageContains('Postcode or street', type: 'error');
    $session_assert->statusMessageContains('Postcode', type: 'error');

    // Now fill in the required postcode and submit again.
    $session_assert->buttonExists('Can\'t find the address?')->click();
    $this->submitForm(edit: [
      'does_it_have_an_address' => 'Yes',
      'it_s_address[postcode]'  => 'XM4 5HQ',
    ], submit: 'Submit');
    $session_assert->waitForElementVisible('css', '.webform-confirmation__message');

    $session_assert->pageTextContains('Thank you, your form has been successfully submitted.');
  }

  /**
   * A form with only an address element with a required postcode.
   *
   * Submitting the form with an empty postcode should produce error
   * irrespective of whether the address has been selected following a lookup or
   * manually added or altered.
   */
  public function testStandaloneAddress(): void {

    $page           = $this->getSession()->getPage();
    $session_assert = $this->assertSession();

    $this->drupalGet('/webform/address_error_message_test_form4');

    // Fill in the postcode and search for address.
    $postcode_or_street_textfield = $page->find('css', '#edit-address-address-lookup-address-search-address-searchstring');
    $this->assertNotEmpty($postcode_or_street_textfield);
    $postcode_or_street_textfield->setValue('BN1 1JE');

    $search_btn = $page->find('css', '#edit-address-address-lookup-address-search-address-actions-address-searchbutton');
    $this->assertNotEmpty($search_btn);
    $search_btn->click();
    $session_assert->waitForElementVisible('css', '[data-drupal-selector=edit-address-address-lookup-address-select-address-select-list]');

    // Select an address from the dropdown.
    $address_dropdown = $page->find('css', '[data-drupal-selector=edit-address-address-lookup-address-select-address-select-list]');
    $this->assertNotEmpty($address_dropdown);
    $address_dropdown->selectOption('000022062038');
    $session_assert->waitForElementVisible('css', '#edit-address-address-1');

    // Empty postcode field.
    $postcode_textfield = $page->find('css', '#edit-address-postcode');
    $postcode_textfield->setValue('');

    // Submitting the form with an empty postcode should produce error.
    $submit_btn = $page->find('css', '#edit-submit');
    $this->assertNotEmpty($submit_btn);
    $submit_btn->click();
    $session_assert->waitForElementVisible('css', '.messages--error');

    $session_assert->statusMessageContains('Postcode', type: 'error');
  }

}
