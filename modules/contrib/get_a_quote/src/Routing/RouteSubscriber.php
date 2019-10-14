<?php
/**
 * @file
 * Contains \Drupal\get_a_quote\Routing\RouteSubscriber.
 */
 
// THIS FILE BELONGS AT /get_a_quote/src/Routing/RouteSubscriber.php
namespace Drupal\get_a_quote\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Replace "some.route.name" below with the actual route you want to override.
    if ($route = $collection->get('commerce_checkout.form')) {
		 
      /* $route->setDefaults(array(
        '_controller' => '\Drupal\example_module\Controller\ExampleModuleController::content',
      )); */
    } 
  }
}