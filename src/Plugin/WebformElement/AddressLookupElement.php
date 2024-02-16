<?php

declare(strict_types=1);

namespace Drupal\localgov_forms\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElementBase;

/**
 * Placeholder for the localgov_forms_address_lookup element.
 *
 * The localgov_webform_uk_address Webform element relies on this element.
 *
 * @WebformElement(
 *   id = "localgov_forms_address_lookup",
 *   label = @Translation("LocalGov Address select"),
 *   description = @Translation("Address lookup element."),
 *   category = @Translation("LocalGov Forms"),
 *   hidden = TRUE,
 *   multiline = TRUE,
 * )
 */
class AddressLookupElement extends WebformElementBase {}
