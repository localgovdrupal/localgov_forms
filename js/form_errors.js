/**
 * @file
 *  Webform form errrors.
 */
(function ($, Drupal) {

  /**
   * Click handler for error links.
   *
   * @param  {event} event
   *   Jquery click event.
   */
  var link_click = function (event) {

    // Get the target from jQuery event data.
    var target = event.data.target;

    // Set focus on click to the first input.
    var closet_input = target.find('input, textarea').first();
    closet_input.focus();
  }

  // Attach after an ajax refresh
  Drupal.behaviors.localgov_forms_errors = {
    attach: function (context, settings) {
      $('.localgov-forms-alert-content ul > li > a', context).each(function () {

        // Get fragment link
        var fragment = $(this).attr('href');

        // If is a fragment (to avoid regular links in the banner).
        if (fragment.indexOf('#') === 0) {

          // If there is a wrapper target, apply the click handler.
          var target = $(fragment + '--wrapper');
          if (target.length > 0) {
            $(this).on('click', {target: target}, link_click);
          }
        }

      });
    }
  }

})(jQuery, Drupal);
