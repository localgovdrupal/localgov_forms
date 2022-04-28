/**
 * @file JS file for moving webform
 * error messages above the erroneous form input
 * fields. As defined in the
 * https://design-system.service.gov.uk/components/error-summary/
 *
 */

(function (Drupal) {
  Drupal.behaviors.placeErrorAboveInput = {
    attach: function (context, settings) {
      // Form field.
      const formField = document.querySelectorAll(
        'input[type="text"].required.error, input[type="email"].required.error,  textarea, input[type="text"].form-text.error'
      );

      formField.forEach((formField) => {
        // Email and Text fields
        if (formField.type == "email" || formField.type == "text") {
          // LocalGov Date Field input consists of three text input fields
          // Day, Month and Year. We want all date validation error message
          // to appear undere the fields label as  per GDS pattern
          if (
            // Check if it's a date part input field
            formField.className == "localgov_forms_date__day form-text error" ||
            formField.className == "localgov_forms_date__month form-text error" ||
            formField.className == "localgov_forms_date__year form-text error"
          ) {
            // Check if the error message is beneath the
            // date part input field. If so move it above it.
            if (formField.parentElement.parentNode.nextElementSibling) {
              formField.parentElement.parentNode.insertBefore(
                formField.parentElement.parentNode.nextElementSibling,
                formField.parentElement
              );
            }
          } else {
            // Webform Text Input or Email input fields
            formField.parentNode.insertBefore(
              formField.nextElementSibling,
              formField
            );
          }
        }
        // Webform Textarea Input fields
        else if (formField.type == "textarea") {
          formField.parentNode.insertBefore(
            formField.parentElement.nextElementSibling,
            formField
          );
        }
      });
    },
  };
})(Drupal);
