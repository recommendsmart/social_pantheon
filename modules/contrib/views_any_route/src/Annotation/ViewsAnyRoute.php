<?php

namespace Drupal\views_any_route\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ViewsAnyRoute annotation object.
 *
 * Plugin Namespace: Plugin\views_any_route .
 *
 * @see plugin_api
 *
 * @Annotation
 */
class ViewsAnyRoute extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the ViewsAnyRoute.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The category under which the ViewsAnyRoute should be listed in the UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category;

}
