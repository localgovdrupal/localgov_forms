<?php

declare(strict_types = 1);

namespace Drupal\localgov_forms_example_liberty_create_integration;

use Drupal\webform\WebformSubmissionInterface;

/**
 * Generates and inserts file tokens for the Liberty Create Rest API.
 *
 * Replaces the
 * `[webform_submission:values:ELEMENT-ID:file_details_for_liberty_create_api]`
 * pseudo-token with a few more tokens based on the number of uploaded files.
 * "Pseudo token" refers to a string that is expanded into several other tokens
 * arranged as a YAML array.
 *
 * The replacement appears like this:
 * @code
 * "{file: {filename: \"[webform_submission:values:files:0:name]\", is_base64: true, content: \"[webform_submission:values:files:0:data]\"}, filename: \"[webform_submission:values:files:0:name]\", description: \"N/A\"}, {file: {filename: \"[webform_submission:values:files:1:name]\", is_base64: true, content: \"[webform_submission:values:files:1:data]\"}, filename: \"[webform_submission:values:files:1:name]\", description: \"N/A\"}"
 * @endcode
 * This is where *two* files have been uploaded to a Webform file element
 * called "files" as part of a Webform submission.
 *
 * Note that in the final output, we do *not* wrap the output YAML snippet in
 * brackets to denote that this is an array.  This must be done in the parent
 * YAML.  This allows us to expand pseudo-tokens for *multiple* file elements
 * within a single array.  Sample YAML from the "Remote post" plugin
 * configuration:
 * @code
 * doc: ["[webform_submission:values:ELEMENT-ID-0:file_details_for_liberty_create_api]", "[webform_submission:values:ELEMENT-ID-1:file_details_for_liberty_create_api]"]
 * @endcode
 */
class PrepareFileTokens {

  /**
   * Replaces all file element pseudo-tokens.
   */
  public static function expandAllPseudoTokens(string $custom_data, WebformSubmissionInterface $webform_submission): string {

    $file_elem_id_list = self::determineAllFileElementId($custom_data);
    if (empty($file_elem_id_list)) {
      return $custom_data;
    }

    $webform = $webform_submission->getWebform();

    $custom_data_w_file_detail_tokens = $custom_data;
    foreach ($file_elem_id_list as $file_elem_id) {
      $file_elem = $webform->getElement($file_elem_id);
      if (empty($file_elem)) {
        continue;
      }

      $file_elem_label = $file_elem['#title'] ?? self::EMPTY_FILE_LABEL;
      $custom_data_w_file_detail_tokens = self::expandPseudoToken($file_elem_id, $file_elem_label, $custom_data_w_file_detail_tokens, $webform_submission);
    }

    return $custom_data_w_file_detail_tokens;
  }

  /**
   * Replaces one pseudo-token with several other tokens.
   *
   * The "several other" tokens mentioned above are prepared dynamically based
   * on the number of uploaded files.
   */
  public static function expandPseudoToken(string $file_elem_id, string $file_elem_label, string $custom_data, WebformSubmissionInterface $webform_submission): string {

    $uploaded_file_count = self::countUploadedFiles($file_elem_id, $webform_submission);

    $file_tokens_for_liberty_create_api = self::prepareInlineTokens($uploaded_file_count, $file_elem_id, $file_elem_label);

    $custom_data_w_file_detail_tokens = str_replace("\"[webform_submission:values:{$file_elem_id}:file_details_for_liberty_create_api]\"", $file_tokens_for_liberty_create_api, $custom_data);

    return $custom_data_w_file_detail_tokens;
  }

  /**
   * Prepares file related tokens as part of an inline YAML snippet.
   *
   * For ease of understanding, this is the conventional block format YAML
   * equivalent of the inline YAML produced here:
   * @code
   * - file:
   *     filename: "[webform_submission:values:files:0:name]"
   *     is_base64: true
   *     content: "[webform_submission:values:files:0:data]"
   *   filename: "[webform_submission:values:files:0:name]"
   *   description: 'Files'
   * - file:
   *     filename: "[webform_submission:values:files:1:name]"
   *     is_base64: true
   *     content: "[webform_submission:values:files:1:data]"
   *   filename: "[webform_submission:values:files:1:name]"
   *   description: 'Files'
   * @endcode
   * This assumes two files have been uploaded to the "files" Webform element.
   *
   * @todo Use Symfony\Component\Yaml\Dumper::dump()?
   */
  public static function prepareInlineTokens(int $file_count, string $file_elem_id, string $file_elem_label): string {

    $file_detail_tokens = [];

    for ($i = 0; $i < $file_count; $i++) {
      $file_elem_label_escaped = str_replace("'", "''", $file_elem_label);
      $file_detail_tokens[] = "{file: {filename: \"[webform_submission:values:{$file_elem_id}:{$i}:name]\", is_base64: true, content: \"[webform_submission:values:{$file_elem_id}:{$i}:data]\"}, filename: \"[webform_submission:values:{$file_elem_id}:{$i}:name]\", description: '{$file_elem_label_escaped}'}";
    }

    $all_file_detail_tokens_inline_yaml = implode(', ', $file_detail_tokens);
    return $all_file_detail_tokens_inline_yaml;
  }

  /**
   * Uploaded file count.
   *
   * Counts files uploaded to a Webform element as part of a Webform submission.
   */
  public static function countUploadedFiles(string $file_elem_id, WebformSubmissionInterface $webform_submission): int {

    $uploaded_file_ids = $webform_submission->getElementData($file_elem_id);

    $uploaded_file_count = $uploaded_file_ids ? count($uploaded_file_ids) : 0;
    return $uploaded_file_count;
  }

  /**
   * Webform file element machine ids.
   *
   * Extracts the file element machine ids used in the Liberty Create file
   * pseudo tokens.
   *
   * Example:
   * The `[webform_submission:values:foo:file_details_for_liberty_create_api]`
   * pseudo-token is using the "foo" machine id of a file Webform element.
   */
  public static function determineAllFileElementId(string $custom_data): ?array {

    $matches = [];
    $has_file_elem = preg_match_all('#"\[webform_submission:values:(?<file_elem_id>\w+):file_details_for_liberty_create_api\]"#m', $custom_data, $matches);

    if ($has_file_elem) {
      return $matches['file_elem_id'];
    }

    return NULL;
  }

  /**
   * Fallback file field label.
   */
  const EMPTY_FILE_LABEL = 'N/A';

}
