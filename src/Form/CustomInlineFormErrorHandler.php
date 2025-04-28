<?php

namespace Drupal\localgov_forms\Form;

use Drupal\Core\Form\FormElementHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\inline_form_errors\FormErrorHandler as BaseInlineFormErrorHandler;
// No need to re-import MessengerInterface, RendererInterface, etc. if you don't override the constructor

/**
 * Overrides the inline form error handler to customize the summary message.
 */
class CustomInlineFormErrorHandler extends BaseInlineFormErrorHandler {

  /**
   * {@inheritdoc}
   *
   * Overrides the method to change the summary error message markup.
   */
  protected function displayErrorMessages(array $form, FormStateInterface $form_state) {
    // Skip generating inline form errors when opted out (keep parent logic).
    if (!empty($form['#disable_inline_form_errors'])) {
      // Use parent::parent::displayErrorMessages to call the Core handler directly
      // if you want to completely bypass the inline_form_errors logic when disabled.
      // Or just parent::displayErrorMessages if inline_form_errors's parent call is sufficient.
      // Let's stick to the original module's logic flow for consistency:
      parent::displayErrorMessages($form, $form_state);
      return;
    }

    $error_links = [];
    $errors = $form_state->getErrors();
    // Loop through all form errors and check if we need to display a link.
    // (This part is identical to the parent method)
    foreach ($errors as $name => $error) {
      $form_element = FormElementHelper::getElementByName($name, $form);
      $title = FormElementHelper::getElementTitle($form_element);

      $is_visible_element = Element::isVisibleElement($form_element);
      $has_title = !empty($title);
      $has_id = !empty($form_element['#id']);

      if (!empty($form_element['#error_no_message'])) {
        unset($errors[$name]);
      }
      elseif ($is_visible_element && $has_title && $has_id) {
        $error_links[] = Link::fromTextAndUrl($title, Url::fromRoute('<none>', [], ['fragment' => $form_element['#id'], 'external' => TRUE]))->toRenderable();
        unset($errors[$name]);
      }
    }

    // Set normal error messages for all remaining errors (identical to parent).
    foreach ($errors as $error) {
      $this->messenger->addError($error);
    }

    // If there are links to specific errors, create the summary message.
    if (!empty($error_links)) {
      $render_array = [
        [
          // Use the static string, wrapped in t() for potential translation.
          '#markup' => '<h4 class="error-summary__title">' . $this->t('There is a problem') .'</h4>',
        ],
        [
          // Keep the list of links (identical to parent).
          '#theme' => 'item_list',
          '#items' => $error_links,
          '#context' => ['list_style' => 'comma-list'],
        ],
      ];
      // Render and add the message (identical to parent).
      $message = $this->renderer->renderInIsolation($render_array);
      $this->messenger->addError($message);
    }
  }

}
