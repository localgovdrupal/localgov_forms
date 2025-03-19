<?php

namespace Drupal\localgov_forms\Plugin\Block;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\TitleResolver;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\localgov_forms\Event\FormHeaderDisplayEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'FormHeaderBlock' block.
 *
 * @Block(
 *  id = "localgov_forms_form_header_block",
 *  admin_label = @Translation("Form header block"),
 * )
 */
class FormHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $currentRouteMatch;
  protected $eventDispatcher;
  protected $requestStack;
  protected $titleResolver;
  protected $entity = NULL;
  protected $formTitle;
  protected $currentPage;
  protected $wizardPageTitle;
  protected $formSummary;
  protected $visible;
  protected $cacheTags;
  protected $formState;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $current_route_match, ContainerAwareEventDispatcher $event_dispatcher, RequestStack $request_stack, TitleResolver $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $current_route_match;
    $this->eventDispatcher = $event_dispatcher;
    $this->requestStack = $request_stack;
    $this->titleResolver = $title_resolver;

    // Find the entity, if any, associated with the current route.
    $route = $this->currentRouteMatch->getRouteObject();
    if ($route !== NULL) {
      $parameters = $route->getOption('parameters');
      if ($parameters !== NULL) {
        foreach ($parameters as $name => $options) {
          if (!isset($options['type'])) {
            continue;
          }

          if (strpos($options['type'], 'entity:') === 0) {
            $entity = $this->currentRouteMatch->getParameter($name);
          }
          elseif ($options['type'] === 'node_preview') {
            $preview = $this->currentRouteMatch->getParentRouteMatch()->getParameter($name);
            if (isset($preview->preview_view_mode) && $preview->preview_view_mode === 'full') {
              $entity = $preview;
            }
          }

          if (isset($entity) && $entity instanceof EntityInterface) {
            $this->entity = $entity;
            break;
          }
        }
      }
    }

    // Dispatch event to allow modules to alter block content.
    $event = new FormHeaderDisplayEvent($this->entity);
    $this->eventDispatcher->dispatch($event, FormHeaderDisplayEvent::EVENT_NAME);

    // Set the Form title, current page, form summary, visibility and cache tags.
    $this->formTitle = $event->getFormTitle() === NULL ? $this->getFormTitle() : $event->getFormTitle();
    $this->currentPage = $event->getCurrentPage() === NULL ? $this->getCurrentPage() : $event->getCurrentPage();//
    $this->wizardPageTitle = $event->getWizardPageTitle() === NULL ? $this->getWizardPageTitle() : $event->getWizardPageTitle();
    $this->formSummary = $event->getFormSummary() === NULL ? $this->getFormSummary() : $event->getFormSummary();
    $this->visible = $event->getVisibility();

    $entityCacheTags = $this->entity === NULL ? [] : $this->entity->getCacheTags();
    $this->cacheTags = $event->getCacheTags() === NULL ? $entityCacheTags : $event->getCacheTags();
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('event_dispatcher'),
      $container->get('request_stack'),
      $container->get('title_resolver')
    );
  }

  public function build(FormStateInterface $form_state = NULL) {;

    $build = [];

    if ($form_state) {
      $this->currentPage = $form_state->get('current_page');
      $currentPage = $this->currentPage;
      $wizard_pages = $this->entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Add the form title to the beginning of the array.
      // so that page indexes start from 1
      array_unshift($page_keys, $this->formTitle);

      $page_title = $wizard_pages[$currentPage]["#title"];
      $this->wizardPageTitle = $page_title;

    }

    $build[] = [
      '#theme' => 'localgov_forms_form_header_block',
      '#formTitle' => $this->formTitle,
      '#wizardPageTitle' => $this->wizardPageTitle,
      '#currentPage' => $this->currentPage,
      '#formSummary' => $this->formSummary,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

  protected function getFormTitle() {
    $request = $this->requestStack->getCurrentRequest();
    $route = $this->currentRouteMatch->getRouteObject();
    if ($route) {
      return $this->titleResolver->getTitle($request, $route);
    }
    return NULL;
  }

  protected function getCurrentPage() {


    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {

      $wizard_pages = $this->entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Get the current page index from the request.
      $request = $this->requestStack->getCurrentRequest();
      $current_page_index = $request->query->get('page');

      if ($current_page_index !== NULL) {
        return $current_page_index;
      }
      else {
        $currentPage = reset($page_keys);
      }

      return $currentPage;
    }
    return NULL;
  }
  protected function getWizardPageTitle() {
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {
      $currentPage = $this->currentPage;
      $wizard_pages = $this->entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Add the form title to the beginning of the array.
      // so that page indexes start from 1
      array_unshift($page_keys, $this->formTitle);

      $page_title = isset($page_keys[$currentPage]) ? $wizard_pages[$page_keys[$currentPage]]["#title"] : NULL;

      return $page_title;
    }
    return NULL;
  }

  protected function getFormSummary() {
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {
      return $this->entity->getDescription();
    }
    return NULL;
  }

  protected function blockAccess(AccountInterface $account) {
    if ($this->visible) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::neutral();
    }
  }

  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  public function getCacheTags() {
    if (!empty($this->cacheTags)) {
      return Cache::mergeTags(parent::getCacheTags(), $this->cacheTags);
    }
    return parent::getCacheTags();
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }
}