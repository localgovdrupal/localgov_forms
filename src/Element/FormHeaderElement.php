<?php

namespace Drupal\localgov_forms\Element;

use Drupal\Core\Render\Element\FormElementBase;

/**
 * Provides a LocalGov Forms form header element'.
 *
 * @FormElement("localgov_forms_header")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\your_module\Element\YourModuleWebformElement
 */
class FormHeaderElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#input' => FALSE,
    ];
  }

}
