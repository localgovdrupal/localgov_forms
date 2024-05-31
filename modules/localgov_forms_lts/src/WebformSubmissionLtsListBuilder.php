<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\webform\WebformSubmissionListBuilder;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * List builder for Webform submissions.
 *
 * Provides a list controller for Webform submission entity data stored in
 * Long term storage.
 */
class WebformSubmissionLtsListBuilder extends WebformSubmissionListBuilder implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   *
   * We retain the "view" and "notes" operations only.
   */
  public function getDefaultOperations(EntityInterface $entity) {

    if (!$entity instanceof WebformSubmissionInterface) {
      return [];
    }

    $webform_submission    = $entity;
    $webform_submission_id = $webform_submission->id();
    $webform_id            = $webform_submission->getWebform()->id();

    $ops = parent::getDefaultOperations($entity);

    $lts_ops = [
      'view'  => $ops['view'] ?? [],
      'notes' => $ops['notes'] ?? [],
    ];
    $lts_ops['view']['url'] = Url::fromRoute('entity.webform_submission.lts_view', [
      'webform'     => $webform_id,
      'webform_sid' => $webform_submission_id,
    ]);
    $lts_ops['notes']['url'] = Url::fromRoute('entity.webform_submission.lts_notes', [
      'webform'     => $webform_id,
      'webform_sid' => $webform_submission_id,
    ]);

    return $lts_ops;
  }

  /**
   * {@inheritdoc}
   *
   * Tells the list builder to use our Webform submissions LTS storage.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Drupal\webform\WebformSubmissionLtsListBuilder $instance */
    $instance = parent::createInstance($container, $entity_type);

    $lts_storage = LtsStorageForWebformSubmission::createInstance($container, $entity_type);
    $instance->storage = $lts_storage;
    $instance->initialize();
    $instance->columns = $instance->storage->getSubmissionsColumns();

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    $webform_sub_def = $container->get('entity_type.manager')->getDefinition('webform_submission');
    return self::createInstance($container, $webform_sub_def);
  }

}
