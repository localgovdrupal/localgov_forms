<?php

namespace Drupal\localgov_forms;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the form_error_handler service to use our custom handler.
 */
class LocalGovFormsServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Check if the core service definition exists.
    if ($container->hasDefinition('form_error_handler')) {
      $definition = $container->getDefinition('form_error_handler');

      // Set the class to *your* custom handler.
      $definition->setClass(\Drupal\localgov_forms\Form\CustomInlineFormErrorHandler::class);

      // Ensure the arguments match the constructor of the class being replaced
      // (Drupal\inline_form_errors\FormErrorHandler in this case).
      // If your CustomInlineFormErrorHandler didn't change the constructor,
      // these arguments inherited from the parent class are correct.
      $definition->setArguments([
        new Reference('string_translation'),
        new Reference('renderer'),
        new Reference('messenger'),
      ]);
      // If you added dependencies in your custom class constructor, adjust arguments here.
    }
  }

}
