<?php

namespace Drupal\views_any_route;

/**
 * An interface for all ViewsAnyRoute type plugins.
 */
interface ViewsAnyRouteInterface {

  /**
   * Provide a description of the plugin.
   *
   * @return string
   *   A string description of the plugin.
   */
  public function description();

}
