<?php

/**
 * @file
 * Post deployment hook implementations.
 */

declare(strict_types=1);

use Drupal\Component\Render\MarkupInterface;
use Drupal\localgov_forms_lts\Constants;
use Drupal\localgov_forms_lts\LtsCopy;

/**
 * Implements hook_deploy_NAME().
 *
 * Copies Webform submissions into the LTS database.  Uses a batch job for the
 * copy operation.
 */
function localgov_forms_lts_deploy_copy_webform_subs(array &$sandbox): MarkupInterface {

  if (!localgov_forms_lts_has_db()) {
    return t('The LocalGov Forms LTS database must exist for this module to function.');
  }

  $lts_copy_obj = LtsCopy::create(Drupal::getContainer());
  $copy_results = $lts_copy_obj->copy();

  $sandbox['#finished'] = (count($copy_results) < Constants::COPY_LIMIT) ? 1 : 0;

  $feedback = _localgov_forms_lts_prepare_feedback_msg($copy_results);
  Drupal::service('logger.factory')
    ->get('localgov_forms_lts')
    ->info($feedback);

  return $feedback;
}
