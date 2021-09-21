<?php

namespace Drupal\localgov_forms_date\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\DateList;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'localgov_forms_date' element.
 *
 * @WebformElement(
 *   id = "localgov_forms_date",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Datetime!Element!Datelist.php/class/Datelist",
 *   label = @Translation("LocalGov Forms Date"),
 *   description = @Translation("Provides a form element for date selection text fields."),
 *   category = @Translation("LocalGov Forms"),
 * )
 */
class LocalgovFormsDate extends DateList{

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties()
  {
    return [
      'date_min' => '',
      'date_max' => '',
      // Date settings.
      'date_part_order' => ['day', 'month', 'year'],
      'date_text_parts' => ['day', 'month', 'year'],
      'date_year_range' => '1900:2050',
      //'date_year_range_reverse' => FALSE,
      'date_increment' => 1,
      'date_abbreviate' => TRUE,
      '#options_display' => 'side_by_side',
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state)
  {
    $form = parent::form($form, $form_state);
    $form['date']['#title'] = $this->t('Date list settings');
/*      $form['date']['date_part_order_label'] = [
      '#type' => 'item',
      '#title' => $this->t('Date part and order'),
      '#description' => $this->t("Select the date parts and order that should be used in the element."),
      '#access' => TRUE,
    ]; */
    $form['date']['date_part_order'] = [
      '#type' => 'webform_tableselect_sort',
      '#header' => ['part' => 'Date part'],
      '#options' => [
/*         'day' => ['part' => $this->t('Days')],
        'month' => ['part' => $this->t('Months')],
        'year' => ['part' => $this->t('Years')],
        'hour' => ['part' => $this->t('Hours')],
        'minute' => ['part' => $this->t('Minutes')],
        'second' => ['part' => $this->t('Seconds')],
        'ampm' => ['part' => $this->t('AM/PM')], */
        ],
    ];
    $form['date']['date_text_parts'] = [
      '#type' => 'checkboxes',
      '#options_display' => 'side_by_side',
       '#title' => $this->t('Date text parts'),
     '#description' => $this->t("Select date parts that should be presented as text fields instead of drop-down selectors."),
      '#options' => [
/*         'day' => $this->t('Days'),
        'month' => $this->t('Months'),
        'year' => $this->t('Years'),
        'hour' => $this->t('Hours'),
        'minute' => $this->t('Minutes'),
        'second' => $this->t('Seconds'), */
      ],
    ];
    $form['date']['date_year_range'] = [
/*       '#type' => 'textfield',
      '#title' => $this->t('Date year range'),
      '#description' => $this->t("A description of the range of years to allow, like '1900:2050', '-3:+3' or '2000:+3', where the first value describes the earliest year and the second the latest year in the range.") . ' ' .
        $this->t('Use min/max validation to define a more specific date range.'), */
    ];
    $form['date']['date_year_range_reverse'] = [
/*       '#type' => 'checkbox',
      '#title' => $this->t('Date year range reverse'),
      '#description' => $this->t('If checked date year range will be listed from max to min.'),
      '#return_type' => TRUE, */
    ];
    $form['date']['date_increment'] = [
/*       '#type' => 'number',
      '#title' => $this->t('Date increment'),
      '#description' => $this->t('The increment to use for minutes and seconds'),
      '#min' => 1,
      '#size' => 4,
      '#weight' => 10, */
    ];
    $form['date']['date_abbreviate'] = [
/*       '#type' => 'checkbox',
      '#title' => $this->t('Abbreviate month'),
      '#description' => $this->t('If checked, month will be abbreviated to three letters.'),
      '#return_value' => TRUE, */
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $values['date_part_order'] = ['day', 'month', 'year'];
    $values['date_text_parts'] = ['day', 'month', 'year'];
    $form_state->setValues($values);

  }

  /**
   * After build handler for Datelist element.
   */
  public static function afterBuild(array $element, FormStateInterface $form_state)
  {
    $element = parent::afterBuild($element, $form_state);

    //set the property of the date of birth elements
    $element['day']['#attributes']['placeholder'] = t('DD');
    $element['day']['#maxlength'] = 2;
    $element['day']['#attributes']['class'][] = 'person-date--day';

    $element['month']['#attributes']['placeholder'] = t('MM');
    $element['month']['#maxlength'] = 2;
    $element['month']['#attributes']['class'][] = 'person-date--month';

    $element['year']['#attributes']['placeholder'] = t('YYYY');
    $element['year']['#maxlength'] = 4;
    $element['year']['#attributes']['class'][] = 'person-date--year';

    return $element;
  }

}
