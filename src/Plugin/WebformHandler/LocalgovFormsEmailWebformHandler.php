<?php
namespace Drupal\localgov_forms\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;


/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "localgov_forms_email",
 *   label = @Translation("Localgov Forms Email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission via an email."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class LocalgovFormsEmailWebformHandler extends EmailWebformHandler {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
    'states' => [
        WebformSubmissionInterface::STATE_COMPLETED,
        WebformSubmissionInterface::STATE_LOCKED,
    ],
      'to_mail' => static::DEFAULT_VALUE,
      'to_options' => [],
      'cc_mail' => '',
      'cc_options' => [],
      'bcc_mail' => '',
      'bcc_options' => [],
      'from_mail' => static::DEFAULT_VALUE,
      'from_options' => [],
      'from_name' => static::DEFAULT_VALUE,
      'subject' => static::DEFAULT_VALUE,
      'body' => static::DEFAULT_VALUE,
      'excluded_elements' => [],
      'ignore_access' => FALSE,
      'exclude_empty' => TRUE,
      'exclude_empty_checkbox' => FALSE,
      'exclude_attachments' => FALSE,
      'html' => TRUE,
      'attachments' => FALSE,
      'twig' => FALSE,
      'debug' => FALSE,
      'reply_to' => '',
      'return_path' => '',
      'sender_mail' => '',
      'sender_name' => '',
      'theme_name' => '',
      'email_body_format' =>'',
      'parameters' => [],
    ];
  }

    /**
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildConfigurationForm($form, $form_state);

        // How to display file attachment in the email body.
        $form['attachments']['email_body_format'] = [
            '#type' => 'select',
            // '#options' => ['Link to file','File name only'],
            '#options' =>  ['0' => 'Filename Only', '1' => 'Link to file'],
            '#title' => t('Email Body - display attachment as'),
            '#description' => t('Select how the attached files are displayed in the email body'),
            '#return_value' => TRUE,
            // '#default_value' => $settings['submission_exclude_empty'],
            '#states' => [
                'visible' => [':input[name="settings[attachments]"]' => ['checked' => TRUE]
                ],
            ],

        ];

        return $form;


    }



}
