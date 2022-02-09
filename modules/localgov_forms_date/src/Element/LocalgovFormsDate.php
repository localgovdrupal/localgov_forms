<?php

namespace Drupal\localgov_forms_date\Element;

use Drupal\Core\Datetime\Element\Datelist;

/**
 * Provides a datelist element.
 *
 * @FormElement("localgov_forms_date")
 */
class LocalgovFormsDate extends Datelist {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#element_validate' => [
        [$class, 'validateDatelist'],
      ],
      '#process' => [
        [$class, 'processDatelist'],
      ],
      '#theme' => 'localgov_forms_date',
    //  '#theme' => 'datetime_form',
      '#theme_wrappers' => ['localgov_forms_date_wrapper'],
      '#date_part_order' => ['day', 'month', 'year'],
      '#date_text_parts' => ['day', 'month', 'year'],
      '#date_year_range' => '1900:2050',
      '#date_increment' => 1,
      '#date_date_callbacks' => [],
      '#date_timezone' => date_default_timezone_get(),
    ];
  }

}
