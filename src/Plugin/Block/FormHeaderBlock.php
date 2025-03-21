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
   * The current route match service.
   *
   * This service provides information about
   * the current route and its parameters.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The event dispatcher service.
   *
   * This service is used to dispatch events within the application.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The request stack service.
   *
   * This service provides access to the current request and allows
   * interaction with the request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The title resolver service.
   *
   * This service is used to resolve the title for the form header block.
   *
   * @var \Drupal\Core\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The entity associated with the form header block.
   *
   * @var \Drupal\Core\Entity\EntityInterface|null
   *   The entity object or NULL if no entity is set.
   */
  protected $entity = NULL;

  /**
   * The title of the form.
   *
   * @var string
   */
  protected $formTitle;

  /**
   * The current page object.
   *
   * This property holds the current page context, which can be used to
   * determine the page-specific information or behavior.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface|null
   */
  protected $currentPage;

  /**
   * The title of the current wizard page.
   *
   * This property stores the title of the page in a multi-step form wizard.
   *
   * @var string
   */
  protected $wizardPageTitle;

  /**
   * The summary of the form.
   *
   * This property holds a brief description or summary of the form
   * associated with this block.
   *
   * @var string
   */
  protected $userDescription;

  /**
   * Indicates whether the form header block is visible.
   *
   * @var bool
   */
  protected $visible;

  /**
   * The cache tags associated with this block.
   *
   * Cache tags are used to manage cache invalidation for this block.
   *
   * @var array
   */
  protected $cacheTags;

  /**
   * The form state service.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  protected $formState;

  /**
   * Constructs a FormHeaderBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   The event dispatcher service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Render\TitleResolver $title_resolver
   *   The title resolver service.
   *
   * @throws \InvalidArgumentException
   *   Thrown if the entity associated with the current route is invalid.
   *
   *   This constructor initializes the block by:
   *   - Determining the entity associated with the current route, if any.
   *   - Dispatching a FormHeaderDisplayEvent to allow modules
   *    to alter block content.
   *   - Setting the form title, current page, wizard page title,
   *    user description,
   *    visibility, and cache tags based on the event or default values.
   */
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

    // Set the Form title, current page, form summary,
    // visibility and cache tags.
    $this->formTitle = $event->getFormTitle() ?? $this->getFormTitle();
    $this->currentPage = $event->getCurrentPage() ?? $this->getCurrentPage();
    $this->wizardPageTitle = $event->getWizardPageTitle() ?? $this->getWizardPageTitle();
    $this->userDescription = $event->getFormUserDescription() ?? $this->getFormUserDescription();
    $this->visible = $event->getVisibility();

    $entityCacheTags = $this->entity === NULL ? [] : $this->entity->getCacheTags();
    $this->cacheTags = $event->getCacheTags() ?? $entityCacheTags;
  }

  /**
   * Creates an instance of the FormHeaderBlock plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns a new instance of the FormHeaderBlock plugin.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   *   Thrown if a required service is not found in the container.
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
   * Summary of build.
   *
   * @param mixed $form_state
   *   The form state object.
   *
   * @return array|array{#attached: array,
   *   #cache: array{max-age: int,
   *   #currentPage: \Drupal\Core\Routing\RouteMatchInterface|null,
   *   #formTitle: string,
   *   #theme: string, #userDescription: string,
   *   #wizardPageTitle: string
   *   }}
   */
  public function build(?FormStateInterface $form_state = NULL) {
    $build = [];
    if ($form_state) {
      $this->currentPage = $form_state->get('current_page');
      $currentPage = $this->currentPage;
      $wizard_pages = $this->entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Add the form title to the beginning of the array.
      // so that page indexes start from 1.
      array_unshift($page_keys, $this->formTitle);

      $page_title = $wizard_pages[$currentPage]["#title"];
      $this->wizardPageTitle = $page_title;

      $build = [
        '#theme' => 'localgov_forms_form_header_block',
        '#formTitle' => $this->formTitle,
        '#wizardPageTitle' => $this->wizardPageTitle,
        '#currentPage' => $this->currentPage,
        '#userDescription' => $this->userDescription,
        '#cache' => [
          'max-age' => 0,
        ],
        '#attached' => [
          'library' => [
            'localgov_forms/localgov_forms.form_header_block',
          ],
        ],
      ];
    }
    return $build;
  }

  /**
   * Retrieves the title of the current form.
   *
   * This method uses the current request and route to resolve the title
   * of the form. If a route is available, the title is resolved using
   * the title resolver service. If no route is found, it returns NULL.
   *
   * @return string|null
   *   The resolved title of the form, or NULL if no route is available.
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
   * Retrieves the current page index of a webform with wizard pages.
   *
   * This method checks if the current entity is a webform with wizard pages.
   * If so, it retrieves the list of wizard pages and determines the current
   * page index based on the request query parameter 'page'. If the 'page'
   * parameter is not set, it defaults to the first page in the wizard.
   *
   * @return string|int|null
   *   The current page index if available, or NULL if the entity is not a
   *   webform with wizard pages or if no page index is determined.
   */
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
  protected function getWizardPageTitle() {
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {
      $currentPage = $this->currentPage;
      $wizard_pages = $this->entity->getPages();
      $page_keys = array_keys($wizard_pages);

      // Add the form title to the beginning of the array.
      // so that page indexes start from 1.
      array_unshift($page_keys, $this->formTitle);

      $page_title = isset($page_keys[$currentPage]) ? $wizard_pages[$page_keys[$currentPage]]["#title"] : NULL;

      return $page_title;
    }
    return NULL;
  }

  /**
   * Retrieves the user description for the form.
   *
   * This method checks if the current entity is a webform with wizard pages.
   * If so, it retrieves the user description from the third-party settings
   * specific to the "localgov_forms" module. If the conditions are not met,
   * it returns NULL.
   *
   * @return string|null
   *   The user description from the third-party settings, or NULL if the
   *   entity is not a webform with wizard pages or the description is not set.
   */
  protected function getFormUserDescription() {
    if ($this->entity instanceof WebformInterface && $this->entity->hasWizardPages()) {
      // Return $this->entity->getDescription();
      return $this->entity->getThirdPartySetting('localgov_forms', 'user_description');
    }
    return NULL;
  }

  /**
   * Determines access to the block based on its visibility.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account object for which access is being checked.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns AccessResult::allowed() if the block is visible,
   *   otherwise returns AccessResult::neutral().
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
   * Provides the default configuration for the block.
   *
   * @return array
   *   An associative array containing the default configuration values:
   *   - label_display: (bool) Whether the label should be displayed.
   *   Defaults to FALSE.
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * Retrieves the cache tags associated with this block.
   *
   * This method checks if additional cache tags are defined for the block
   * and merges them with the parent cache tags. If no additional cache tags
   * are defined, it simply returns the parent cache tags.
   *
   * @return array
   *   An array of cache tags.
   */
  public function getCacheTags() {
    if (!empty($this->cacheTags)) {
      return Cache::mergeTags(parent::getCacheTags(), $this->cacheTags);
    }
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   *
   * Overrides the cache contexts for the block.
   * Adds the 'route' cache context to ensure the block is cached
   * per route, in addition to the default cache contexts provided
   * by the parent implementation.
   *
   * @return array
   *   An array of cache contexts.
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
