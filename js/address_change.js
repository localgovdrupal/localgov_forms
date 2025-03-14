/**
 * @file
 *  Address change
 */
(function ($, Drupal) {

  /**
   * Add reset button
   * @param {jQuery} addressLookupElement
   *   Address lookup jquery element.
   */
  function addResetButton(addressLookupElement) {

    // Add the reset button.
    var resetButton = $('<button>', {
      text: 'Reset',
      type: 'button',
      class: 'button btn btn-orange js-reset-address hidden',
      href: '#'
    });

    // Reset the form.
    resetButton.click(function () {
      resetAddressLookUpForm(addressLookupElement, $(this), 'hard');

      // Hide manual entry button if asked for.
      if (typeof drupalSettings.centralHub.isManualAddressEntryBtnAlwaysVisible !== 'undefined' && drupalSettings.centralHub.isManualAddressEntryBtnAlwaysVisible) {
        var manualButton = addressLookupElement.parent().find('.js-manual-address');
        manualButton.show();
      }
    });

    // Append the reset button.
    addressLookupElement.find('.js-address-actions').append(resetButton);
  }

  /**
   * Reset Address Lookup Form.
   *
   * @param {jQuery} addressLookupElement
   *   Address lookup element.
   * @param {jQuery} resetButton
   *   Reset button element.
   * @param {String} mode
   *   'soft' = Do not clear the search string.
   *   'hard' = Clear the search string.
   */
  function resetAddressLookUpForm(addressLookupElement, resetButton, mode) {
    addressLookupElement.find('.js-address-select-container').addClass('hidden');
    addressLookupElement.find('.js-address-select').val('0');
    if (mode == 'hard') {
      addressLookupElement.find('.js-address-searchstring').val('').focus();
    }
    resetButton.addClass('hidden');
  }

  /**
   * Populate Central Hub Selected Address
   *
   * Adds the selected address to drupalSettings.centralhub.selectedAddress.
   *
   * @param  {jQuery} selectList
   *   Address selectlist element.
   */
  function populateCentrahubSelectedAddress(selectList) {
    // As the select gets replaced by Drupal, this lets us know its ready.
    selectList.addClass('js-populated');
    selectList.change(function () {
      var addressSelectId = $(this).data('address-id');
      if (drupalSettings['centralHub'][addressSelectId]['addressList']) {
        var addressList = drupalSettings['centralHub'][addressSelectId]['addressList'];
        var addressSelectedName = $(this).val();
        var addressSelected = addressList.find(function (element) { return element.name == addressSelectedName; } );
        drupalSettings.centralHub.selectedAddress = addressSelected;
      }
    });
  }

  // Attach after an ajax refresh
  Drupal.behaviors.localgov_forms = {
    attach: function (context, settings) {

      // Behaviors to only attach once.
      $(once('central-hub-behaviours', '.js-centralhub-address-lookup', context)).each(function () {
        // Get form elements.
        var addressLookupElement = $(this);
        var searchElement = addressLookupElement.find('.js-address-searchstring');
        var searchButton = addressLookupElement.find('.js-address-searchbutton');
        var selectListContainer = addressLookupElement.find('.js-address-select-container');
        var selectList = selectListContainer.find('.js-address-select');
        var error = selectListContainer.find('.js-address-error, .error');

        // Change the search button to normal button.
        searchButton.attr('type', 'button');
        searchButton.click(function (event) {
          event.preventDefault();
          searchButton.addClass('js-searching');
        });

        // Add the reset button.
        if (addressLookupElement.find('.js-reset-address').length == 0) {
          addResetButton(addressLookupElement);
        }
        var resetButton = addressLookupElement.find('.js-reset-address');

        searchElement.unbind('change');
        // Mark search element when changed.
        searchElement.change(function () {
          $(this).addClass('js-changed');
        }).keyup(function (event) {
          // Detect keypress is backspace or actual text key.
          // See : https://www.cambiaresearch.com/articles/15/javascript-char-codes-key-codes.
          var keyCode = event.keyCode;
          if (keyCode == 8 || keyCode == 32 || (keyCode >= 48 && keyCode <= 90) || (keyCode >= 96 && keyCode <= 111) || (keyCode >= 186 && keyCode <= 222)) {
            resetAddressLookUpForm(addressLookupElement, resetButton, 'soft');
          }
          if (keyCode == 13) {
            searchElement.blur();
            event.preventDefault();
            searchButton.trigger('click');
          }
          // Stop the main form submitting on enter.
        }).focus(function () {
          searchButton.attr('type', 'submit');
        }).blur(function () {
          searchButton.attr('type', 'button');
        });

        if (error.length > 0) {
          searchButton.attr('type', 'submit');
        }

        // Default hide the select box and reset button.
        // This is for when address lookup is on the first page.
        if ((selectList.find('option').length == 0 && searchElement.val().length == 0) || error.length == 0) {
          resetButton.addClass('hidden');
          selectListContainer.addClass('hidden');
        }

        // Make sure that selected address is blank.
        // See DRUP-1294.
        // @todo seperate each selected address based on element ID.
        if (typeof drupalSettings.centralHub !== 'undefined') {
          drupalSettings.centralHub.selectedAddress = undefined;
        }

      });

      // Behaviours to add each time.
      // Requires the address select box to be populated.
      $('.js-centralhub-address-lookup', context).each(function () {
        // Get form elements.
        var addressLookupElement = $(this);
        var searchElement = addressLookupElement.find('.js-address-searchstring');
        var searchButton = addressLookupElement.find('.js-address-searchbutton');
        var selectListContainer = addressLookupElement.find('.js-address-select-container');
        var selectList = selectListContainer.find('.js-address-select');
        var error = selectListContainer.find('.js-address-error, .error');
        var resetButton = addressLookupElement.find('.js-reset-address');
        var manualButton = addressLookupElement.find('.js-manual-address');
        var manualAddressContainer = addressLookupElement.find('+ .js-address-entry-container');
        var ajaxProgressElement = addressLookupElement.find('.ajax-progress');

        // This ajac was either not initiated by the address lookup,
        // or the search is still in progress.
        if (!searchButton.hasClass('js-searching') || ajaxProgressElement.length > 0) {
          return;
        }

        // Unhide the manual entry button only when manual address entry fields
        // are hidden.
        if (typeof settings.centralHub.isManualAddressEntryBtnAlwaysVisible !== 'undefined' && !settings.centralHub.isManualAddressEntryBtnAlwaysVisible && manualAddressContainer.is(':hidden')) {
          manualButton.show();
        }

        // If there has been a search.
        if ((selectList.find('option').length > 0 && searchElement.val().length > 0) || error.length > 0) {
          searchButton.removeClass('js-searching');

          // unhide resetbutton.
          resetButton.removeClass('hidden');

          // After search, show the address lookup
          selectListContainer.removeClass('hidden');

          // Add select behaviour if present
          if (selectList.find('option').length > 1) {
            populateCentrahubSelectedAddress(selectList);
            if (searchElement.hasClass('js-changed')) {
              selectList.focus();
              searchElement.removeClass('js-changed');
            } else if (drupalSettings.centralHub.newSearch) {
              // If new search latch set, refocus on search element.
              searchElement.focus();
            }
          } else {
            searchElement.focus();
          }

          // Set newSearch latch to false.
          drupalSettings.centralHub.newSearch = false;

          // Else make sure the select box is hidden.
        } else {
          resetButton.addClass('hidden');
          selectListContainer.addClass('hidden');
        }

      });
    }
  }
//below code handles the logic for Address Lookup dropdown
  $(document).ready(function () {
    // Store references to the clicked fieldset and dropdown
    var clickedFieldset = null;
    var clickedDropdown = null;

    // Log details when a fieldset is clicked
    $('fieldset').click(function () {
      clickedFieldset = $(this);  // Store the clicked fieldset
     });

    // Log details when the search button is clicked
    $('.js-address-searchbutton').click(function () {
      // Store the dropdown related to the clicked button
      clickedDropdown = $(this).closest('fieldset').find('.js-address-select');
     });

    // Handle the dropdown update after AJAX request
    $(document).ajaxComplete(function () {
      if (clickedDropdown) {
        var selectElement = clickedDropdown; // Get the specific dropdown that was clicked

        // Clear previous options
        selectElement.empty();

        // Add default option
        var defaultOption = $('<option>', {
          value: '0',
          text: '-Please choose an address-'
        });
        selectElement.append(defaultOption);

        // Check if address list exists in drupalSettings
        var addressList = drupalSettings.centralHub.addressList;

        if (addressList && addressList.length > 0) {
          // Add addresses to the dropdown
          $.each(addressList, function (index, address) {
            var option = $('<option>', {
              value: address.name,
              text: address.display
            });
            selectElement.append(option);
          });
        } else {
          // If no addresses are found, show a "No addresses found" option
          var noAddressOption = $('<option>', {
            value: '',
            text: 'No addresses found'
          });
          selectElement.append(noAddressOption);
        }

        // Show the dropdown after AJAX loads addresses
        selectElement.closest('.js-address-select-container').removeClass('hidden');
      }
    });
  });
})(jQuery, Drupal);
