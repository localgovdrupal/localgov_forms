<?php

namespace Drupal\localgov_forms\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Container;

/**
 * Provides a wrapper element to group one or more Review elements with buttons.
 *
 * @RenderElement("localgov_forms_review_element")
 */
class ReviewElement extends Container {

  /**
   * Buttons.
   *
   * @var string[]
   */
  public static $buttons = [
    'wizard_prev',
  ];

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#process' => [
        [$class, 'processWebformActions'],
        [$class, 'processContainer'],
      ],
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Processes a form actions container element.
   */
  public static function processWebformActions(&$element, FormStateInterface $form_state, &$complete_form) {
    if (empty($element['#value'])) {
      return $element;
    }

    $prefix = ($element['#webform_key']) ? 'edit-' . $element['#webform_key'] . '-' : '';

    $element['#attributes']['class'][] = 'preview-wrapper';
    // Copy the form's actions to this element.
    $element += $complete_form['actions'];

    foreach (static::$buttons as $button_name) {
      // Make sure the button exists.
      if (!isset($element[$button_name])) {
        continue;
      }

      // Get settings name.
      $settings_name = $button_name;

      // Set unique id for each button.
      if ($prefix) {
        $element[$button_name]['#id'] = Html::getUniqueId("$prefix$button_name");
      }

      // Hide buttons using #access.
      if (!empty($element['#' . $settings_name . '_hide'])) {
        $element[$button_name]['#access'] = FALSE;
      }

      // Apply custom label.
      $element[$button_name]['#value'] = $element['#' . $settings_name . '__label'] ?? 'Change';

      // The #name attribute needs to be unique so triggeringElement can be
      // correctly associated with this button.
      $element[$button_name]['#name'] = $prefix . $button_name;
      $element[$button_name]['#identifier'] = 'change_wizard_prev';

      // Apply attributes (class, style, properties).
      if (!empty($element['#' . $settings_name . '__attributes'])) {
        $element[$button_name] += ['#attributes' => []];
        foreach ($element['#' . $settings_name . '__attributes'] as $attribute_name => $attribute_value) {
          if ($attribute_name === 'class') {
            $element[$button_name]['#attributes'] += ['class' => []];
            // Merge class names.
            $element[$button_name]['#attributes']['class'] = array_merge($element[$button_name]['#attributes']['class'], $attribute_value);
          }
          else {
            $element[$button_name]['#attributes'][$attribute_name] = $attribute_value;
          }
        }
      }

      if (isset($element['#source_page'])) {
        $element[$button_name]['#page'] = $element['#source_page'];
      }
    }

    return $element;
  }

}
