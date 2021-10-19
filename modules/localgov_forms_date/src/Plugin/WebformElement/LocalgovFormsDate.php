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
class LocalgovFormsDate extends DateList {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [
      'date_min' => '',
      'date_max' => '',
      // Date settings.
      'date_part_order' => ['day', 'month', 'year'],
      'date_text_parts' => ['day', 'month', 'year'],
      'date_year_range' => '1900:2050',
      'date_increment' => 1,
      'date_abbreviate' => TRUE,
      '#options_display' => 'side_by_side',
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['date']['#title'] = $this->t('Date list settings');
    $form['date']['date_part_order'] = [
      '#type' => 'webform_tableselect_sort',
      '#header' => ['part' => 'Date part'],
      '#options' => [],
    ];
    $form['date']['date_text_parts'] = [
      '#type' => 'checkboxes',
      '#options_display' => 'side_by_side',
      '#title' => $this->t('Date text parts'),
      '#description' => $this->t("Select date parts that should be presented as text fields instead of drop-down selectors."),
      '#options' => [],
    ];
    $form['date']['date_year_range'] = [];
    $form['date']['date_year_range_reverse'] = [];
    $form['date']['date_increment'] = [];
    $form['date']['date_abbreviate'] = [];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $values['date_part_order'] = ['day', 'month', 'year'];
    $values['date_text_parts'] = ['day', 'month', 'year'];
    $form_state->setValues($values);

  }

  /**
   * After build handler for Datelist element.
   */
  public static function afterBuild(array $element, FormStateInterface $form_state) {
    $element = parent::afterBuild($element, $form_state);

    // Set the property of the date of birth elements.
    $element['day']['#attributes']['placeholder'] = t('DD');
    $element['day']['#maxlength'] = 2;
    $element['day']['#attributes']['inputmode'] = 'numeric';
    $element['day']['#attributes']['pattern'] = '[0-9]*';
    $element['day']['#attributes']['class'][] = 'localgov_forms_date__day';

    $element['month']['#attributes']['placeholder'] = t('MM');
    $element['month']['#maxlength'] = 2;
    $element['month']['#attributes']['inputmode'] = 'numeric';
    $element['month']['#attributes']['pattern'] = '[0-9]*';
    $element['month']['#attributes']['class'][] = 'localgov_forms_date__month';

    $element['year']['#attributes']['placeholder'] = t('YYYY');
    $element['year']['#maxlength'] = 4;
    $element['year']['#attributes']['inputmode'] = 'numeric';
    $element['year']['#attributes']['pattern'] = '[0-9]*';
    $element['year']['#attributes']['class'][] = 'localgov_forms_date__year';

    return $element;
  }

}
