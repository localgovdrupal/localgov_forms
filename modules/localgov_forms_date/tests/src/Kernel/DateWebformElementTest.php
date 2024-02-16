<?php

declare(strict_types=1);

namespace Drupal\Tests\localgov_forms_date\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;

/**
 * Tests for Date element validation.
 */
class DateWebformElementTest extends KernelTestBase {

  /**
   * Tests a webform with our date field.
   */
  public function testFormSubmission() {

    $this->passCase();
    $this->failCaseWithInvalidDay();
    $this->failCaseWithNonNumericDay();
    $this->failCaseWithInvalidYear();
  }

  /**
   * Tests valid date.
   */
  protected function passCase() {

    $form_state = new FormState();
    $form_state->setValue('date',
      ['day' => '1', 'month' => '1', 'year' => '1']);
    $form_state->setValue('op', 'Submission');

    $this->container->get('form_builder')->submitForm(clone($this->testForm), $form_state);
    $this->assertEmpty($form_state->getErrors());
  }

  /**
   * Tests date with invalid day.
   */
  protected function failCaseWithInvalidDay() {

    $form_state = new FormState();
    $form_state->setValue('date', ['day' => '1D']);
    $form_state->setValue('op', 'Submission');

    $this->container->get('form_builder')->submitForm(clone($this->testForm), $form_state);
    $this->assertNotEmpty($form_state->getErrors());
  }

  /**
   * Tests date with nonnumeric day.
   */
  protected function failCaseWithNonNumericDay() {

    $form_state = new FormState();
    $form_state->setValue('date',
      ['day' => 'DD', 'month' => '1', 'year' => '2022']);
    $form_state->setValue('op', 'Submission');

    $this->container->get('form_builder')->submitForm(clone($this->testForm), $form_state);
    $this->assertNotEmpty($form_state->getErrors());
  }

  /**
   * Tests date with invalid year.
   */
  protected function failCaseWithInvalidYear() {

    $form_state = new FormState();
    $form_state->setValue('date',
      ['day' => '1', 'month' => '1', 'year' => 'Last year']);
    $form_state->setValue('op', 'Submission');

    $this->container->get('form_builder')->submitForm(clone($this->testForm), $form_state);
    $this->assertNotEmpty($form_state->getErrors());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() :void {

    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('path_alias');

    $this->installSchema('webform', ['webform']);

    $this->installConfig('system');
    $this->installConfig('localgov_forms_date_test');

    $empty_submission = WebformSubmission::create(['webform_id' => 'date_test']);
    $this->testForm = WebformSubmissionForm::create($this->container);
    $this->testForm->setEntityTypeManager($this->container->get('entity_type.manager'));
    $this->testForm->setModuleHandler($this->container->get('module_handler'));
    $this->testForm->setEntity($empty_submission);
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'path',
    'path_alias',
    'webform',
    'localgov_forms_date',
    'localgov_forms_date_test',
  ];

  /**
   * Webform submission form.
   *
   * @var Drupal\webform\WebformSubmissionForm
   */
  protected $testForm;

}
