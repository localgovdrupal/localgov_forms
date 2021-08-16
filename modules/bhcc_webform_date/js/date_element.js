(function($) {

  /**
   * Hide errors on an element
   * @param  {jQuery} indvElement
   *   The individual form input element to hide errors.
   */
  function hideErrorsOnElement(indvElement) {
    var indvElementWrapper = indvElement.find('.js-form-item');
    indvElementWrapper.removeClass('has-error');
    indvElement.find('input + .invalid-feedback').remove();
  }

  Drupal.behaviors.bhcc_webform_date = {
    attach: function(context, settings) {
      $('.js-webform-type-bhcc-webform-date', context).once('hide-errors-on-element').each(function() {
        hideErrorsOnElement($(this));
      });
    }
  }

}) (jQuery);
