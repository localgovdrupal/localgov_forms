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
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Entity\WebformSubmission;
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

  /**
   * Core current_route_match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Core event_dispatcher service.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Core request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Core title_resolver service.
   *
   * @var \Drupal\Core\Controller\TitleResolver
   */
  protected $titleResolver;

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
  protected $formTitle;


  /**
   * The current page title override.
   *
   * @var array|string|null
   */
  protected $currentPage;

  /**
   * The wizard page title override.
   *
   * @var array|string|null
   */
  protected $wizardPageTitle;

    /**
   * The form Summaryoverride.
   *
   * @var array|string|null
   */
  protected $formSummary;

  /**
   * Should the page header block be displayed?
   *
   * @var bool
   */
  protected $visible;

  /**
   * Cache tags for this block.
   *
   * @var array
   */
  protected $cacheTags;

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $current_route_match, ContainerAwareEventDispatcher $event_dispatcher, RequestStack $request_stack, TitleResolver $title_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRouteMatch = $current_route_match;
    $this->eventDispatcher = $event_dispatcher;
    $this->requestStack = $request_stack;
    $this->titleResolver = $title_resolver;

    // Find the entity, if any, associated with the current route.
    //
    // We consider two cases: (1) type entity:*, and (2) type node_preview with
    // view_mode_id set to 'full'.
    $route = $this->currentRouteMatch->getRouteObject();
    if (!is_null($route)) {
      $parameters = $route->getOption('parameters');
      if (!is_null($parameters)) {
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
    $this->formTitle = is_null($event->getFormTitle()) ? $this->getFormTitle() : $event->getFormTitle();
    $this->currentPage = is_null($event->getCurrentPage()) ? $this->getCurrentPage() : $event->getCurrentPage();
    $this->wizardPageTitle = is_null($event->getWizardPageTitle()) ? $this->getWizardPageTitle() : $event->getWizardPageTitle();
    $this->formSummary = is_null($event->getFormSummary()) ? $this->getFormSummary() : $event->getFormSummary();
    $this->visible = $event->getVisibility();

    $entityCacheTags = is_null($this->entity) ? [] : $this->entity->getCacheTags();
    $this->cacheTags = is_null($event->getCacheTags()) ? $entityCacheTags : $event->getCacheTags();

  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build[] = [
      '#theme' => 'localgov_forms_form_header_block',
      '#formTitle' => $this->formTitle,
      '#wizardPageTitle' => $this->wizardPageTitle,
      '#currentPage' => $this->currentPage,
      '#formSummary' => $this->formSummary,
      '#cache' => [
        'max-age' => 0,
        ]
    ];

    return $build;
  }

  /**
   * Get the Webform Title.
   *
   * @return array|string|null
   *   Returns a title for the current page or NULL if it can't be determined.
   */
  protected function getFormTitle() {
    $request = $this->requestStack->getCurrentRequest();
    $route = $this->currentRouteMatch->getRouteObject();
    if ($route) {
      return $this->titleResolver->getTitle($request, $route);
    }
    return NULL;
  }

  /**
   * Get the current page.
   *
   * @return array|string|null
   *   Returns the current page or NULL if it can't be determined.
   */
  protected function getCurrentPage() {

    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {

        $wizard_pages =  $this->entity->getPages();
        $page_keys = array_keys($wizard_pages);
        // $page_indexes = array_flip($page_keys);

        // Determine the Current Page Index.
        if (!isset($currentPage)) {
            $currentPage = reset($page_keys);

        }

      return $currentPage;
    }
    return NULL;
  }

  /**
   * Get the Wizard Page Title
   * @return array|string|null
   *   Returns the current page or NULL if it can't be determined.
   */
  protected function getWizardPageTitle(){
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {

        // $currentPage = NULL;
        $currentPage =  $this->currentPage;
        $wizard_pages =  $this->entity->getPages();
        $page_keys = array_keys($wizard_pages);
        // $description = $this->entity->getDescription();

        // $number_of_wizard_pages = $this->entity->getNumberOfWizardPages();
        // $page_keys = array_keys($wizard_pages);
        // $page_index = reset($page_keys);


        $page_title = $wizard_pages[$currentPage]["#title"];

      return $page_title;
    }
  }

  /**
   * Get the Wizard Page Title
   * @return array|string|null
   *   Returns the current page or NULL if it can't be determined.
   */
  protected function getFormSummary(){
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {
        $form_summary = $this->entity->getDescription();
      return $form_summary;
    }
  }


  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->visible) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if (!empty($this->cacheTags)) {
      return Cache::mergeTags(parent::getCacheTags(), $this->cacheTags);
    }
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
