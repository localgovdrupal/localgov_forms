<?php

/**
 * @file
 * Token declarations.
 */

declare(strict_types=1);

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\webform\Utility\WebformDateHelper;

/**
 * Implements hook_token_info().
 *
 * Token declarations.
 */
function localgov_forms_token_info() {

  return [
    'tokens' => [
      'webform_submission' => [
        'purge_date' => [
          'name'        => t('Purge date'),
          'description' => t('Purge date for a Webform submission.'),
          'type'        => 'date',
        ],
      ],
    ],
  ];
}

/**
 * Implements hook_tokens().
 *
 * Provides following token values:
 * - purge_date
 * - purge_date:long
 * - purge_date:custom:d/m/Y.
 * - etc.
 *
 * @see system_tokens()
 */
function localgov_forms_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {

  $replacements = [];

  if ($type === 'webform_submission' && !empty($data['webform_submission'])) {
    $webform_submission = $data['webform_submission'];

    $purge_days = $webform_submission->getWebform()->getSetting('purge_days');
    $purge_ts   = $webform_submission->getCreatedTime() + ((int) $purge_days * 24 * 60 * 60);

    if (isset($tokens['purge_date']) && $purge_days) {
      $bubbleable_metadata->addCacheableDependency(DateFormat::load('medium'));

      $orig_token = $tokens['purge_date'];
      $replacements[$orig_token] = WebformDateHelper::format($purge_ts, 'medium', '');
    }

    $token_service = Drupal::service('token');
    if (($purge_tokens = $token_service->findWithPrefix($tokens, 'purge_date')) && $purge_days) {
      $replacements += $token_service->generate('date', $purge_tokens, [
        'date' => $purge_ts,
      ], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
