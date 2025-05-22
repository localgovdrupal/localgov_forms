<?php

namespace Drupal\localgov_forms\EventSubscriber;

use Drupal\localgov_core\Event\PageHeaderDisplayEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;


/**
 * Display web form header.
 *
 * @package Drupal\localgov_forms\EventSubscriber
 */
class FormHeaderSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PageHeaderDisplayEvent::EVENT_NAME => ['prepareFormHeader', 0],
    ];
  }

  /**
   * Set the form title, user description and current wizard page.
   *
   * @see Drupal\localgov_core\Plugin\Block\PageHeaderBlock::__construct()
   * @see Drupal\localgov_core\Event\PageHeaderDisplayEvent
   */
  public function prepareFormHeader(PageHeaderDisplayEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof WebformInterface) {
      $event->setVisibility(TRUE);

      // Retrieve the webform title.
      $formTitle = $entity->label();

      // Retrieve the custom user description.
      $formUserDescription = $entity->getThirdPartySettings('localgov_forms')['user_description'] ?? '';

      // Retrieve the current page number.
      // $this->setWizardPageSubtitle($entity, $event, );.
      $formCurrentPage = $this->getCurrentPage($entity,);

      $formCurrentPageTitle = $this->getWizardPageTitle($formCurrentPage, $entity);

      // Wizard Page Title.
      if ($formCurrentPageTitle) {
        $event->setSubTitle(['#markup' => $formCurrentPageTitle,'#cache' =>  ['contexts' => [ 'url']]]);
      }

      // Set the form title, lede, and subtitle.
      if ($formTitle) {
        $event->setTitle($formTitle);
    }



      // Set the user description as the lede.
      if ($formUserDescription) {
        $event->setLede($formUserDescription);
      }

    }

  }

  /**
   * Retrieves the current page number in a webform.
   */
  protected function getCurrentPage($entity) {
    // $entity = $event->getEntity();
    if ($entity instanceof WebformInterface && $entity->hasWizardPages()) {

      $wizard_pages = $entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Get the current page index from the request.
      $request = \Drupal::service('request_stack')->getCurrentRequest();
      $current_page_index = $request->query->get('page');

      if ($current_page_index !== NULL) {
        return $current_page_index;
      }
      else {
        $formCurrentPage = reset($page_keys);
      }

      return $formCurrentPage;
    }
    return NULL;
  }

  /**
   * Retrieves the title of the current wizard page in a webform.
   *
   * This method checks if the associated entity is a webform with wizard pages.
   * If so, it determines the title of the current page based on the wizard's
   * page structure. The form title is prepended to the page keys to ensure
   * page indexes start from 1.
   *
   * @return string|null
   *   The title of the current wizard page, or NULL if the entity is not a
   *   webform with wizard pages or if the current page title cannot be
   *   determined.
   */
  protected function getWizardPageTitle($formCurrentPage, $entity) {
    if ($entity instanceof WebformInterface && $entity->hasWizardPages()) {
      // $currentPage = $this->currentPage;
      // Form Title.
      $formTitle = $entity->label();

      $wizard_pages = $entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Add the form title to the beginning of the array.
      // so that page indexes start from 1.
      array_unshift($page_keys, $formTitle);

      // If the current page is not numeric, that suggests we
      // sare on the first page of a multipage form.
      if (!is_numeric($formCurrentPage)) {
        $formCurrentPageTitle = $wizard_pages[$formCurrentPage]["#title"];
        // Convert the current page index to an integer.
        // $formCurrentPage = (int) $formCurrentPage;.
      }
      else {
        // If the current page is not numeric, set it to 0.
        $formCurrentPageTitle = isset($page_keys[$formCurrentPage]) ? $wizard_pages[$page_keys[$formCurrentPage]]["#title"] : NULL;
      }

      return $formCurrentPageTitle;
    }
    return NULL;
  }


}
