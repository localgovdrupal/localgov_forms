<?php

namespace Drupal\localgov_forms\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'webform_uk_address' Webform element.
 *
 * @WebformElement(
 *   id = "webform_uk_address",
 *   label = @Translation("UK Address"),
 *   description = @Translation("Provides a UK address entry webform element."),
 *   category = @Translation("Composite elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformUKAddress extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    $lines = [];
    $lines[] =
      ($value['address_1'] ? $value['address_1'] : '') .
      ($value['address_2'] ? ' ' . $value['address_2'] : '') .
      ($value['town_city'] ? ' ' . $value['town_city'] : '') .
      ($value['postcode'] ? ' ' . $value['postcode'] : '');
    return $lines;
  }

}
