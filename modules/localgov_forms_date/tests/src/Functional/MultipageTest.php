<?php

declare(strict_types = 1);

namespace Drupal\Tests\localgov_forms_date;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests localgov_forms_date.
 *
 * Tests the LocalGov Forms Date Webform element in the context of a Multipage
 * Webform.
 */
class MultipageTest extends BrowserTestBase {

  /**
   * Loading a filled in localgov date elememt should not cause PHP exception.
   *
   * Testing the fix for this PHP exception:
   * "Exception: Error: Call to a member function format() on string"
   *
   * Test steps:
   * - Uses a two page Webform.  Both pages have a LocalGov Forms Date Webform
   *   element.
   * - Fills in the date field on the first page and proceeds to the next page.
   * - Clicks the "Previous" button to return to the first page.
   */
  public function testReturnToPreviousPage() :void {

    // Load the first page of the form.
    $this->drupalGet('/webform/localgov_date_test_form');

    // Fill in the date element and then proceed to the next page.
    $form_values = [
      'first_date[day]'   => 1,
      'first_date[month]' => 2,
      'first_date[year]'  => 2003,
    ];
    $this->submitForm($form_values, 'Next');

    // Try to return to the previous page.  This should not generate any error.
    $this->submitForm([], 'Previous');
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_forms_date',
    'localgov_forms_date_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
