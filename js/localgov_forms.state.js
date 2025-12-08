/**
 * @file
 * Additional JavaScript behaviors for webform #states.
 */

(function ($, Drupal) {

  'use strict';

  const $document = $(document);
  $document.on('state:required', (e) => {
    // Add or remove '(optional)' from element label.
    if (e.trigger) {
      $(e.target)
        .find('span.localgov-form-optional')
        .html(e.value ? '' : Drupal.t('(optional)'));
    }
  });

})(jQuery, Drupal);
