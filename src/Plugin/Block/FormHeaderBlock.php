<?php

namespace Drupal\localgov_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "form_header_block",
 *   admin_label = @Translation("Form Header Block"),
 * )
 */
class FormHeaderBlock extends BlockBase {


public function build() {
    $build = [];

    $build[] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-header-block']],
      'label' => [
        '#markup' => '<h2>Form Header</h2>',
      ],
      'description' => [
        '#markup' => '<p>This is a form header description.</p>',
      ],
      'page_title' => [
        '#markup' => '<p>Current page title</p>',
      ],
    ];

    // Dynamically populate the content if needed.
    // For example, using a webform's label and description:
    $webform_label = 'Your Webform Label';
    $webform_description = 'Your Webform Description';
    $page_title = 'Your Current Page Title';

    $content['label']['#markup'] = '<h2>' . $webform_label . '</h2>';
    $content['description']['#markup'] = '<p>' . $webform_description . '</p>';
    $content['page_title']['#markup'] = '<p>Current page: ' . $page_title . '</p>';

    return $build;


  }
}