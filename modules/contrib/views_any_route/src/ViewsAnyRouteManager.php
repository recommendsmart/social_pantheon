<?php

namespace Drupal\views_any_route;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class ViewsAnyRouteManager .
 *
 * @package Drupal\views_any_route
 *
 * Manages views_any_route plugins.
 */
class ViewsAnyRouteManager extends DefaultPluginManager {

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    /*
     * This tells the plugin system to look for plugins in the
     * 'Plugin/views_any_route' subfolder inside modules' 'src' folder.
     */
    $subdir = 'Plugin/views_any_route';
    /*
     * The name of the interface that plugins should adhere to.
     * Drupal will enforce this as a requirement.
     */
    $plugin_interface = 'Drupal\views_any_route\ViewsAnyRouteInterface';
    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = 'Drupal\views_any_route\Annotation\ViewsAnyRoute';
    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);
    /*
     * This allows the plugin definitions to be altered by an alter hook.
     * The parameter defines the name of the hook,
     * thus: views_any_route_info_alter().
     */
    $this->alterInfo('views_any_route_info');
    // This sets the caching method for our plugin definitions.
    $this->setCacheBackend($cache_backend, 'views_any_route_info');
  }

}
