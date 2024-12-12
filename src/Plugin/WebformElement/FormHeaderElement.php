<?php

namespace Drupal\localgov_forms\Plugin\WebformElement;


use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformMarkup;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'LocalGov Forms Header' element.
 *
 * @WebformElement(
 *   id = "localgov_forms_header",
 *   label = @Translation("Form Header"),
 *   description = @Translation("Displays the webform's title and description."),
 *   category = @Translation("Markup elements"),
 * )
 */
class FormHeaderElement extends WebformMarkup {


  /**
   * Declares and overrides properties.
   *
   * Overrides the Form elememnt's title.
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return parent::defineDefaultProperties() + [
        'title' => 'Form Header',

      ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);

    $webform = $webform_submission->getWebform();

    $current_page = $webform_submission->getCurrentPage();

    // Pages.
    $current_page_title = NULL;

    $pages = $webform->getPages();
    $page_keys = array_keys($pages);
    $page_indexes = array_flip($page_keys);
    $total_pages = count($page_keys);

    // Detremine the Current Page.
    if (!isset($current_page)) {
        $current_page = reset($page_keys);
    } else {
        $current_page = $webform_submission->getCurrentPage();
    }

    // The Page title.
    $current_page_title = $pages[$current_page]['#title'];

    $total_pages;

    // The Form Header Element.

    // Form Title.
    $element['#markup'] = '<div class="webform-title"><h1>' . $webform->label() . '</h1></div>';
    // Form Description.
    $element['#markup'] .= '<div class="webform-description">' . $webform->getDescription() . '</div>';
    // Page Title.
    $element['#markup'] .= '<div class="webform-page-title"><h2>' . $current_page_title . '</h2></div>';
    // Horizontal Rule.
    $element['#markup'] .= '<hr>';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Remove unnecessary settings.
    unset($form['element_attributes']);
    unset($form['wrapper_attributes']);
    unset($form['markup']['markup']);
    // Read only the headeer form element's title so that it
    // cannot be edited.
    $form["element"]["title"]['#disabled'] = TRUE;

    return $form;
  }

}