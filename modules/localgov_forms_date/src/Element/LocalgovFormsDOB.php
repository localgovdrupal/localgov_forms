<?php

namespace Drupal\localgov_forms_date\Element;

use Drupal\Core\Datetime\Element\Datelist;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a datelist element.
 *
 * @FormElement("localgov_forms_dob")
 */
class LocalgovFormsDOB extends LocalgovFormsDate {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $parentInfo = parent::getInfo();
    $childInfo = [
      '#description' => 'For example 08/02/1982',
    ];
    $returnInfo = array_replace($parentInfo, $childInfo);
    return $returnInfo;
  }

}
