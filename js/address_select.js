/**
 * @file
 *  Address change
 */
(function ($, Drupal) {

  /**
   * Add manual entry button
   * @param {jQuery} centralHubElement
   *   Centralhub address element.
   */
  function addManualEntryButton(centralHubElement) {

    // Add the manual lookup button.
    var manualButton = $('<button>', {
      text: 'Can\'t find the address?',
      type: 'button',
      class: 'link-button manual-address js-manual-address',
      href: '#'
    });

    // Reset the form.
    manualButton.click(function () {
      showManualAddress(centralHubElement);
    });

    // Append the manual lookup button.
    centralHubElement.find('.js-centralhub-address-lookup').append(manualButton);
  }

  /**
   * Hide manual address form
   * @param  {jQuery} centralHubElement
   *   Central hub address lookup element.
   * @param  {String} type
   *   'soft' = Do not clear the address values.
   *            (used when an address is selected)
   *   'hard' = Clear the address values.
   */
  function hideManualAddress(centralHubElement, type) {
    var manualAddressContainer = centralHubElement.find('.js-address-entry-container');
    var manualButton = centralHubElement.find('.js-manual-address');
    var addressSelectContainer = centralHubElement.find('.js-address-select-container');
    manualAddressContainer.addClass('hidden');
    if (type == 'hard') {

      // Clear all values.
      manualAddressContainer.find('input').val('');

      // Trigger change events so UPRN and extra fields are cleared.
      manualAddressContainer.find('input').first().trigger('change');
    }
    manualButton.removeClass('hidden');
  }

  /**
   * Show the manual address form elements.
   * @param  {jQuery} centralHubElement
   *   Centralhub address element.
   */
  function showManualAddress(centralHubElement) {
    var manualAddressContainer = centralHubElement.find('.js-address-entry-container');
    var manualButton = centralHubElement.find('.js-manual-address');
    var addressSelectContainer = centralHubElement.find('.js-address-select-container');
    var addressSelect = addressSelectContainer.find('select');
    var searchElement = centralHubElement.find('.js-address-searchstring');
    var addressError = addressSelectContainer.find('.js-address-error');
    manualAddressContainer.removeClass('hidden');
    // manualAddressContainer.find('input').val('');
    manualButton.addClass('hidden');
    // addressSelectContainer.addClass('hidden');
    // addressSelect.val('0');
    // Clear the search element when entering a manual address.
    // This is to pass validation.
    // searchElement.val('');
    // Remove the error element when making a manual address.
    addressError.remove();
  }

  /**
   * Check if a manual address has been entered.
   * @param  {jQuery}  centralHubElement
   *   Centralhub address element.
   * @return {Boolean}
   *   True if the a manual address is present and the search box is empty.
   */
  function isManualAddressEntered(centralHubElement) {
    var manualAddressContainer = centralHubElement.find('.js-address-entry-container');
    var searchElement = centralHubElement.find('.js-address-searchstring');
    // Test manual address if the element is visible.
    if (manualAddressContainer.is(':visible')) {
      var hasManualValue = false
      manualAddressContainer.find('input[type="text"]').each(function () {
        if ($(this).val() != '') {
          hasManualValue = true;
        }
      });
      return hasManualValue;
    }
    return false;
  }

  /**
   * Hide errors on an element
   * @param  {jQuery} indvElement
   *   The individual form input element to hide errors.
   */
  function hideErrorsOnElement(indvElement) {
    var indvElementWrapper = indvElement.closest('.js-form-item');
    indvElement.not('.js-address-searchstring').removeClass('error');
    indvElementWrapper.removeClass('has-error');
    indvElementWrapper.find('.invalid-feedback').remove();
  }

  /**
   * Hide address search errors.
   *
   * On webform, when a form fails validation, errors can cascade to the child
   * elements, including the search box.
   * This will remove the errors in javascript, leaving the error message on
   * the parent element only.
   * @see https://www.drupal.org/project/drupal/issues/2848319
   * @param  {jQuery} centralHubElement
   *   Centralhub address element.
   */
  function hideAddressSearchErrors(centralHubElement) {
    var searchElementContainer = centralHubElement.find('.js-address-search-container');
    var searchElement = searchElementContainer.find('.js-address-searchstring');
    var manualAddressContainer = centralHubElement.find('.js-address-entry-container');
    hideErrorsOnElement(searchElement);
    if (manualAddressContainer.is(':hidden')) {
      manualAddressContainer.find('input').each(function () {
        hideErrorsOnElement($(this));
      });
    }
  }

  /**
   * @var function
   *
   * Central hub select change handler.
   * Populates the address fields when selecting an address.
   */
  var localgov_forms_webform_change_handler = function () {
    // Guard check, don't run if centralhub not yet defined.
    if (typeof drupalSettings.centralHub === 'undefined') {
      return;
    }
    var centralHubElement = $(this).closest('.js-webform-type-localgov-webform-uk-address');
    var central_hub_webform_address_container = $(this).closest('.js-webform-type-localgov-webform-uk-address');
    var central_hub_webfrom_address_entry = central_hub_webform_address_container.find('.js-address-entry-container');

    if (drupalSettings.centralHub.selectedAddress) {
      var addressSelected = drupalSettings.centralHub.selectedAddress;
      central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--address-1').val(addressSelected.line1);
      central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--address-2').val(addressSelected.line2);
      central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--town-city').val(addressSelected.town);
      central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--postcode').val(addressSelected.postcode);

      // add UPRN
      central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--uprn').val(addressSelected.uprn);

      // Add any extra fields from centrahub for Twig access.
      // @See DRUP-1287.
      var extra_elements = ['lat', 'lng', 'ward'];
      $.each(extra_elements, function (index, value) {
        central_hub_webform_address_container.find('input.js-localgov-forms-webform-uk-address--' + value).val(addressSelected[value]);
      });

      // hideManualAddress(centralHubElement, 'soft');
      showManualAddress(centralHubElement);
    } else if ($(this).val() == 0) {
      // If choosing the empty option, clear out the address fields.
      hideManualAddress(centralHubElement, 'hard');
    }
  };

  /**
   * @var function
   *
   * Central hub manual address change handler.
   * Clears any central hub values such as UPRN from the address handler.
   */
  var localgov_forms_webform_manual_address_change_handler = function () {
    var central_hub_webform_address_container = $(this).closest('.js-webform-type-localgov-webform-uk-address');
    var central_hub_webfrom_address_entry = $(this).closest('.js-address-entry-container');

    // Clear UPRN.
    central_hub_webfrom_address_entry.find('input.js-localgov-forms-webform-uk-address--uprn').val('');

    // Clear any extra fields from centrahub for Twig access.
    // @See DRUP-1287.
    var extra_elements = ['lat', 'lng', 'ward'];
    $.each(extra_elements, function (index, value) {
      central_hub_webform_address_container.find('input.js-localgov-forms-webform-uk-address--' + value).val('');
    });
  };

  // Attach after an ajax refresh
  Drupal.behaviors.localgov_forms_webform = {
    attach: function (context, settings) {
      $('.js-webform-type-localgov-webform-uk-address', context).once('localgov-address-webform').each(function () {
        var centralHubElement = $(this);
        addManualEntryButton(centralHubElement);
        // Hide the manual address element, if it has no values.
        if (!isManualAddressEntered(centralHubElement)) {
          hideManualAddress(centralHubElement, 'soft');
        }
        // centralHubElement.find('.js-address-searchstring').change(function() {
        //   hideManualAddress(centralHubElement, 'hard');
        // });
        centralHubElement.find('.js-reset-address').click(function () {
          hideManualAddress(centralHubElement, 'hard');
        });
        hideAddressSearchErrors(centralHubElement);
      });

      // Manual address change handler first.
      $(document).once('.js-address-entry-container input', context).ajaxSuccess(function (event, data) {
        $('.js-address-entry-container input').on('change', localgov_forms_webform_manual_address_change_handler);
      });

      // Select box change handler.
      $(document).once('.js-address-select-container', context).ajaxSuccess(function (event, data) {
        $('.js-address-select').on('change', localgov_forms_webform_change_handler);
      });
    },
    detach: function (context, settings, trigger) {
      $('.js-address-entry-container input').off('change', localgov_forms_webform_manual_address_change_handler);
      $('.js-address-select').off('change', localgov_forms_webform_change_handler);
    }
  }

})(jQuery, Drupal);
