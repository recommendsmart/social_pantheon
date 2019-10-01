<?php

namespace Drupal\nbox_ui\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class NboxRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.nbox.add_form')) {
      $route->setOption('_admin_route', FALSE);
    }
    if ($route = $collection->get('entity.nbox_folder.add_form')) {
      $route->setOption('_admin_route', FALSE);
    }
    if ($route = $collection->get('entity.nbox_folder.edit_form')) {
      $route->setOption('_admin_route', FALSE);
    }
  }

}
