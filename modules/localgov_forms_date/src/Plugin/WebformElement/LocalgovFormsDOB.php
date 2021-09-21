<?php

namespace Drupal\localgov_forms_date\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\DateList;
use Drupal\Core\Form\FormStateInterface;

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
class LocalgovFormsDOB extends LocalgovFormsDate{



  /**
   * {@inheritdoc}
   */
/*   protected function defineDefaultProperties()
  {

    return [
      //'#date_date_min' => '1900',
      //'#date_date_max' => 'today',
      'date_date_min' => '1900',
      'date_date_max' => 'today',
    ] + parent::defineDefaultProperties();

  } */

  /**
   * {@inheritdoc}
   */
/*   public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $values['#date_date_min'] = '1900';
    $values['#date_date_max'] = 'today';
    $values['date_date_min'] = '1900';
    $values['date_date_max'] = 'today';
    $form_state->setValues($values);
  } */

}
