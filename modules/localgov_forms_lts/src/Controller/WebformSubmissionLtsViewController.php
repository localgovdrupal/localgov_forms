<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts\Controller;

use Drupal\localgov_forms_lts\LtsStorageForWebformSubmission;
use Drupal\webform\Controller\WebformSubmissionViewController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission view as seen in Long term storage.
 */
class WebformSubmissionLtsViewController extends WebformSubmissionViewController {

  /**
   * Webform submission view callback.
   *
   * Loads the Webform submission from Long term storage.
   */
  public function viewFromLts(int $webform_sid, $view_mode = 'default', $langcode = NULL) {

    $webform_sub = $this->ltsStorage->load($webform_sid);
    return parent::view($webform_sub, $view_mode, $langcode);
  }

  /**
   * Webform submission notes callback.
   *
   * Loads the Webform submission from Long term storage.
   */
  public function noteViewFromLts(int $webform_sid, $view_mode = 'default', $langcode = NULL) {

    $webform_sub = $this->ltsStorage->load($webform_sid);
    return [
      '#markup' => '<pre>' . $webform_sub->getNotes() . '</pre>',
    ];
  }

  /**
   * Entity title callback.
   *
   * Loads the Webform submission from Long term storage.
   */
  public function titleFromLts(int $webform_sid, $duplicate = FALSE) {

    $webform_sub = $this->ltsStorage->load($webform_sid);
    return parent::title($webform_sub, $duplicate);
  }

  /**
   * Factory.
   */
  public static function create(ContainerInterface $container) {

    $instance = parent::create($container);

    $webform_sub_entity_type = $container->get('entity_type.manager')->getDefinition('webform_submission');
    $instance->ltsStorage = LtsStorageForWebformSubmission::createInstance($container, $webform_sub_entity_type);

    return $instance;
  }

  /**
   * Database service for the Long term storage database.
   *
   * @var Drupal\webform\WebformSubmissionStorageInterface
   */
  protected $ltsStorage;

}
