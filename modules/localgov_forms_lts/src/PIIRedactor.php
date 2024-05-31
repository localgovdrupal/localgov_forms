<?php

declare(strict_types=1);

namespace Drupal\localgov_forms_lts;

use Drupal\webform\WebformSubmissionInterface;

/**
 * Personally Identifiable Information redactor.
 *
 * Redacts Personally Identifiable Information (PII) from Webform submissions.
 *
 * Redaction rules:
 * - All Webform elements of type email, telephone, number.
 * - Any element with the following happening in its machine id: name, mail,
 *   phone, date_of_birth, personal, title, gender, sex, ethnicity.
 */
class PIIRedactor {

  /**
   * Redacts all PII from given Webform submission.
   *
   * After the redaction, a note is added to the Webform submission to highlight
   * the redacted elements.
   *
   * A list of redacted elements is returned.
   */
  public static function redact(WebformSubmissionInterface $webform_sub) :array {

    $elems_to_redact = static::findElemsToRedact($webform_sub);

    $redaction_result = array_map(function ($elem) use ($webform_sub) {
      if ($webform_sub->getElementData($elem)) {
        $webform_sub->setElementData($elem, NULL);

        return $elem;
      }
    }, $elems_to_redact);

    $redacted_elems = array_filter($redaction_result);
    static::addRedactionNote($webform_sub, $redacted_elems);

    return $redacted_elems;
  }

  /**
   * Finds the Webform element names to redact.
   */
  public static function findElemsToRedact(WebformSubmissionInterface $webform_sub) :array {

    $elem_type_mapping = static::listElemsAndTypes($webform_sub);
    $pii_mapping = array_intersect($elem_type_mapping, static::PII_ELEMENT_TYPES);
    $pii_elems = array_keys($pii_mapping);

    $potential_mapping = array_intersect($elem_type_mapping, static::POTENTIAL_PII_ELEMENT_TYPES);
    $guessed_pii_elems = preg_grep(static::GUESSED_PII_ELEM_PATTERN, array_keys($potential_mapping));

    $elems_to_redact = [...$pii_elems, ...$guessed_pii_elems];
    return $elems_to_redact;
  }

  /**
   * Prepares mapping of element ids and types.
   */
  public static function listElemsAndTypes(WebformSubmissionInterface $webform_sub) :array {

    $elems = $webform_sub->getWebform()->getElementsDecodedAndFlattened();
    return array_map(fn($elem_def) => $elem_def['#type'], $elems);
  }

  /**
   * Adds redaction note.
   *
   * Adds a note to the Webform submission to highlight the redacted elements.
   */
  public static function addRedactionNote(WebformSubmissionInterface $webform_sub, array $redacted_elems) :void {

    if (empty($redacted_elems)) {
      return;
    }

    $redaction_note = 'Redacted elements: ' . implode(', ', $redacted_elems) . '.';

    $existing_note = $webform_sub->getNotes();
    $updated_note  = $existing_note . PHP_EOL . $redaction_note;

    $webform_sub->setNotes($updated_note);
  }

  /**
   * Element types carrying PII for certain.
   */
  const PII_ELEMENT_TYPES = [
    'address',
    'email',
    'localgov_webform_uk_address',
    'number',
    'tel',
    'webform_name',
    'webform_address',
    'webform_contact',
    'webform_telephone',
  ];

  /**
   * Element types that *may* carry PII.
   */
  const POTENTIAL_PII_ELEMENT_TYPES = [
    'textfield',
    'processed_text',
    'checkboxes',
    'radios',
  ];

  /**
   * Preg pattern.
   *
   * Element type naming pattern indicating possible link with PII.
   */
  const GUESSED_PII_ELEM_PATTERN = '#name|mail|phone|contact_number|date_of_birth|dob_|nino|address|postcode|post_code|personal_|title|passport|serial_number|reg_number|pcn_|driver_#i';

}
