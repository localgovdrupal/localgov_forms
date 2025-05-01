<?php

namespace Drupal\localgov_forms\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when displaying the page header.
 */
class FormHeaderDisplayEvent extends Event {

  const EVENT_NAME = 'localgov_forms.form_header_display';

  /**
   * Entity associated with the current route.
   *
   * @var \Drupal\Core\Entity\EntityInterface|null
   */
  protected $entity = NULL;

  /**
   * The form title override.
   *
   * @var array|string|null
   */
  protected $formTitle = NULL;

  /**
   * The wizard page title override.
   *
   * @var array|string|null
   */
  protected $wizardPageTitle = NULL;

  /**
   * The current page title override.
   *
   * @var array|string|null
   */
  protected $currentPage = NULL;

  /**
   * The current page title override.
   *
   * @var array|string|null
   */
  protected $pageIndex = NULL;

  /**
   * The Form user Description override.
   *
   * @var array|string|null
   */
  protected $userDescription = NULL;


  /**
   * Should the page header block be displayed?
   *
   * @var bool
   */
  protected $visibility = TRUE;

  /**
   * Cache tags override.
   *
   * @var array|null
   */
  protected $cacheTags = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity) {
    $this->entity = $entity;
  }

  /**
   * Entity getter.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Form Title getter.
   *
   * @return array|string|null
   *   The form title
   */
  public function getFormTitle() {
    return $this->formTitle;
  }

  /**
   * Form Title setter.
   *
   * @param array|string|null $formTitle
   *   The form title.
   */
  public function setFormTitle($formTitle) {
    $this->formTitle = $formTitle;
  }

  /**
   * Wizard Page Title getter.
   *
   * @return array|string|null
   *   The title.
   */
  public function getWizardPageTitle() {
    return $this->wizardPageTitle;
  }

  /**
   * Wizard Page Title setter.
   *
   * @param array|string|null $wizard_page_title
   *   The title.
   */
  public function setWizardPageTitle($wizard_page_title) {
    $this->wizardPageTitle = $wizard_page_title;
  }

  /**
   * Current page getter.
   *
   * @return array|string|null
   *   The current page title.
   */
  public function getCurrentPage() {
    return $this->currentPage;
  }

  /**
   * Current page setter.
   *
   * @param array|string|null $current_page
   *   The current page title.
   */
  public function setCurrentPage($current_page) {
    $this->currentPage = $current_page;
  }

  /**
   * Form User Description getter.
   *
   * @return array|string|null
   *   The User description title
   */
  public function getFormUserDescription() {
    return $this->userDescription;
  }

  /**
   * Form Summary setter.
   *
   * @param array|string|null $form_summary
   *   The form summary.
   */
  public function setFormSummary($form_summary) {
    $this->userDescription = $form_summary;
  }

  /**
   * Visibility getter.
   *
   * @return bool|null
   *   The title.
   */
  public function getVisibility() {
    return $this->visibility;
  }

  /**
   * Visibility setter.
   *
   * @param bool $visibility
   *   The visibility.
   */
  public function setVisibility($visibility) {
    $this->visibility = $visibility;
  }

  /**
   * Cache tags getter.
   *
   * @return array|null
   *   Cache tags array if set.
   */
  public function getCacheTags() {
    return $this->cacheTags;
  }

  /**
   * Cache tags setter.
   *
   * @param array $cacheTags
   *   The cache tags.
   */
  public function setCacheTags(array $cacheTags) {
    $this->cacheTags = $cacheTags;
  }

}
