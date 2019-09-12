<?php

namespace Drupal\views_any_route\Controller;

use Drupal\views_any_route\ViewsAnyRouteManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ViewsAnyRouteController.
 *
 * Provides the route and API controller for views_any_route.
 *
 * @package Drupal\views_any_route\Controller
 */
class ViewsAnyRouteController extends ControllerBase {

  /**
   * The plugin manager.
   *
   * @var \Drupal\views_any_route\ViewsAnyRouteManager
   */
  protected $ViewsAnyRouteManager;

  /**
   * ViewsAnyRouteController constructor.
   *
   * @param \Drupal\views_any_route\ViewsAnyRouteManager $plugin_manager
   *   The plugin manager object.
   */
  public function __construct(ViewsAnyRouteManager $plugin_manager) {
    $this->ViewsAnyRouteManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /*
     * Use the service container to instantiate
     * a new instance of our controller.
     */
    return new static($container->get('plugin.manager.views_any_route'));
  }

}
