<?php

namespace Drupal\localgov_forms\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElementBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a LGD review element.
 *
 * @WebformElement(
 *   id = "localgov_forms_review_element",
 *   label = @Translation("Review"),
 *   description = @Translation("Show values from another element for review, and link to change."),
 *   category = @Translation("LocalGov Forms"),
 * )
 */
class ReviewElement extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'title' => '',
      'description' => '',
      'source_component' => '',
      'source_link' => '',
      'wizard_prev_hide' => FALSE,
      'wizard_prev__label' => 'Change',
      'wizard_prev__attributes' => [],
    ];

    $properties += $this->defineDefaultBaseProperties();

    unset($properties['#wrapper_attributes']);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['custom_preview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Custom Preview settings'),
    ];
    $form['custom_preview']['source_component'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source Component'),
      '#description' => $this->t('Enter the machine name of the component(s) to display value(s) from. Use a comma separated list of components to show more than one.'),
      '#required' => TRUE,
    ];
    $form['custom_preview']['source_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source component to link to'),
      '#description' => $this->t('Enter the machine name of the component or page to link to with the change/back link. Leave blank to automatically calculate it.'),
      '#required' => FALSE,
    ];
    $name = 'wizard_prev';
    $form[$name . '_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => -10,
      '#title' => $this->t('Change button'),
    ];
    $form[$name . '_settings']['description'] = [
      '#markup' => '<p>' . $this->t('Optionally modify the change button') . '</p>',
      '#access' => TRUE,
    ];
    $form[$name . '_settings'][$name . '_hide'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide change button'),
      '#return_value' => TRUE,
    ];
    $form[$name . '_settings'][$name . '__label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change button label'),
      '#description' => $this->t('Defaults to: %value', ['%value' => 'Change']),
      '#size' => 20,
      '#attributes' => [
        // Make sure default value is never cleared by #states API.
        // @see js/webform.states.js
        'data-webform-states-no-clear' => TRUE,
      ],
      '#states' => [
        'visible' => [':input[name="properties[' . $name . '_hide]"]' => ['checked' => FALSE]],
      ],
    ];
    $form[$name . '_settings'][$name . '__attributes'] = [
      '#type' => 'webform_element_attributes',
      '#title' => $this->t('Change'),
      '#classes' => $this->configFactory->get('webform.settings')->get('settings.button_classes'),
      '#states' => [
        'visible' => [':input[name="properties[' . $name . '_hide]"]' => ['checked' => FALSE]],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, ?WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);

    if ($webform_submission) {
      // Split the source component field into an array if it is a
      // comma separated list.
      $source_components = explode(',', $element['#source_component']);
      $results = [];

      // Iterate over the source_component array and build the formatted output.
      foreach ($source_components as $source_component) {
        $source_element = $webform_submission->getWebform()->getElement(trim($source_component));

        if ($source_element) {
          if ($source_element['#type'] === 'webform_wizard_page') {
            $formatted_output = $this->getWizardPageElementsOutput($source_element);
            if (!empty($formatted_output)) {
              $results[] = [
                'output' => $formatted_output,
                'type' => $source_element['#type'],
                'source_page' => $source_component,
              ];
            }
          }
          else {
            $formatted_output = $this->getElementOutput($source_element);
            if (!empty($formatted_output)) {
              $webform = $webform_submission->getWebform();
              $results[] = [
                'output' => $formatted_output,
                'type' => $source_element['#type'],
                'source_page' => $this->findSourceComponentPage($webform, $source_component),
              ];
            }
          }
        }

      }

      if (empty($results)) {
        return;
      }

      if (!empty($element['#source_link'])) {
        $source_element = $webform_submission->getWebform()->getElement(trim($element['#source_link']));
        if ($source_element) {
          $webform = $webform_submission->getWebform();
          $source_page_link = $source_element['#type'] === 'webform_wizard_page' ? $element['#source_link'] : $this->findSourceComponentPage($webform, $source_component);
        }
      }
      else {
        $source_page_link = reset($results)['source_page'];
      }

      if ($results > 1) {
        // Make a new array containing only 'output'.
        $items = array_column($results, 'output');
        // Output results in an unordered list.
        $compiled_output = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#items' => $items,
        ];
      }
      else {
        $compiled_output = $results[0]['output'];
      }

      // Assign the rendered output to the custom preview element.
      $element['#label'] = $element['#title'] ?? $source_element['#title'];
      $element['#theme'] = 'localgov_forms_review_element';
      $element['#value'] = $compiled_output;

      if ($source_page_link) {
        $element['#source_page'] = $source_page_link;
      }

    }
  }

  /**
   * Gets the rendered output of the source element.
   *
   * @param array $source_element
   *   The source element.
   *
   * @return string
   *   The rendered output of the source element.
   */
  private function getElementOutput(array $source_element) {
    $webform_submission = $this->getWebformSubmission();

    // Render the source component using its formatHtml method.
    $plugin_manager = \Drupal::service('plugin.manager.webform.element');
    $source_component_plugin = $plugin_manager->createInstance($source_element['#type']);
    $formatted_output = $source_component_plugin->formatHtml($source_element, $webform_submission, []);

    return $formatted_output;
  }

  /**
   * Gets the rendered output of all source elements in a wizard page.
   *
   * @param array $page_element
   *   The wizard page element.
   *
   * @return array|null
   *   An array of rendered output of all source elements in the wizard page.
   */
  private function getWizardPageElementsOutput(array $page_element) {
    $webform_submission = $this->getWebformSubmission();
    $element_items = [];

    foreach ($page_element['#webform_children'] as $source_component) {
      $source_element = $webform_submission->getWebform()->getElement($source_component);
      $element_output = $this->getElementOutput($source_element);
      if (!empty($element_output)) {
        $element_items[] = $element_output;
      }
    }

    if (!empty($element_items)) {
      return [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $element_items,
      ];
    }

    return NULL;
  }

  /**
   * Helper function to find the page containing the source component.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   The webform.
   * @param string $source_component
   *   The source component key.
   *
   * @return string|null
   *   The page key containing the source component, or NULL if not found.
   */
  private function findSourceComponentPage(WebformInterface $webform, $source_component) {
    $elements = $webform->getElementsInitialized();

    // If there are no wizard pages, the component is on the main form.
    if (!$this->hasWizardPages($elements)) {
      return '';
    }

    foreach ($elements as $key => $element) {
      if (isset($element['#type']) && $element['#type'] === 'webform_wizard_page') {
        if ($this->pageContainsComponent($element, $source_component)) {
          return $key;
        }
      }
    }

    return NULL;
  }

  /**
   * Check if the webform has wizard pages.
   *
   * @param array $elements
   *   The webform elements.
   *
   * @return bool
   *   TRUE if the webform has wizard pages, FALSE otherwise.
   */
  private function hasWizardPages(array $elements) {
    foreach ($elements as $element) {
      if (isset($element['#type']) && $element['#type'] === 'webform_wizard_page') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Check if a page contains the source component.
   *
   * @param array $page_element
   *   The page element to check.
   * @param string $source_component
   *   The key of the source component.
   *
   * @return bool
   *   TRUE if the page contains the component, FALSE otherwise.
   */
  private function pageContainsComponent(array $page_element, $source_component) {
    if (isset($page_element[$source_component])) {
      return TRUE;
    }

    foreach ($page_element as $element) {
      if (is_array($element) && $this->pageContainsComponent($element, $source_component)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtmlItem(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    // Should not display any output.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function formatTextItem(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    // Should not display any output.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

}
