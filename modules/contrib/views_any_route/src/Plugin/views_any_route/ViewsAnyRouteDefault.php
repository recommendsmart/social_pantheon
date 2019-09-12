<?php

namespace Drupal\views_any_route\Plugin\views_any_route;

use Drupal\Core\Link;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_any_route\ViewsAnyRouteInterface;
use Symfony\Component\Routing\Route;

/**
 * Default plugin for Views Any Route.
 *
 * @ViewsAnyRoute(
 *   id = "views_any_route_default",
 *   label = @Translation("ViewsAnyRouteDefault"),
 * )
 */
class ViewsAnyRouteDefault extends PluginBase implements ViewsAnyRouteInterface {

  /**
   * Plugin description.
   *
   * @return string
   *   A string description.
   */
  public function description() {
    return $this->t('Default Views Add Button URL Generator for entitites which do not have a dedicated ViewsAnyRoute plugin');
  }

  public static function checkAccess($route, array $params) {
    $accessManager = \Drupal::service('access_manager');
    return $accessManager->checkNamedRoute($route, $params, \Drupal::currentUser());
  }

  /**
   * Generate the URL.
   * @param $route
   *  A string representing the chosen Drupal Route.
   * @param array $params
   *  An array of route parameters
   * @param array $options
   *  An array of options for the URL.
   * @return Url
   *  The Url that will be used to construct the link.
   */
  public static function generateUrl($route, array $params, array $options) {
    // Create URL from the data above.
    $url = Url::fromRoute($route, $params, $options);

    return $url;
  }

  /**
   *
   * @param Url $url
   * @param $text
   * @return Link
   */
  public static function generateLink(Url $url, $text) {
    // Create URL from the data above.
    $link = Link::fromTextAndUrl($text, $url);

    return $link;
  }

}
