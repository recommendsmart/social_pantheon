<?php
/**
 * @file
 */

namespace Drupal\views_add_button\Controller;

use Drupal\views_add_button\ViewsAddButtonManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ViewsAddButtonController
 *
 * Provides the route and API controller for views_add_button.
 */
class ViewsAddButtonController extends ControllerBase
{

  protected $ViewsAddButtonManager; //The plugin manager.

  /**
   * Constructor.
   *
   * @param \Drupal\views_add_button\ViewsAddButtonManager $plugin_manager
   */

  public function __construct(ViewsAddButtonManager $plugin_manager) {
    $this->ViewsAddButtonManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   * This is dependancy injection at work for a controller. Rather than access the global service container via \Drupal::service(), it's best practice to use dependency injection.
   */
  public static function create(ContainerInterface $container) {
    // Use the service container to instantiate a new instance of our controller.
    return new static($container->get('plugin.manager.views_add_button'));
  }
}