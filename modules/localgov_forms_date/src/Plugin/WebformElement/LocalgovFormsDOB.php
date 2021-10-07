<?php

namespace Drupal\localgov_forms_date\Plugin\WebformElement;

/**
 * Provides a 'localgov_forms_dob' element.
 *
 * @WebformElement(
 *   id = "localgov_forms_dob",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datelist.php/class/Datelist",
 *   label = @Translation("LocalGov Forms Date of Birth"),
 *   description = @Translation("Provides a form element for date selection text fields."),
 *   category = @Translation("LocalGov Forms"),
 * )
 */
class LocalgovFormsDOB extends LocalgovFormsDate {

}
