<?php

namespace Drupal\views_any_route;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Class ViewsAnyRouteUtilities
 * @package Drupal\views_any_route
 */
class ViewsAnyRouteUtilities {

  /**
   * Build Bundle Type List.
   */
  public static function createPluginList() {
    $plugin_manager = \Drupal::service('plugin.manager.views_any_route');
    $plugin_definitions = $plugin_manager->getDefinitions();

    $options = [];
    foreach ($plugin_definitions as $pd) {
      $label = $pd['label'];
      if ($pd['label'] instanceof TranslatableMarkup) {
        $label = $pd['label']->render();
      }
      $options[$pd['id']] = $label;
    }
    return $options;
  }

  /**
   * @param $delimiter
   * @param $string
   * @return array
   */
  public static function parameterStringToArray($delimiter, $string) {
    $params = [];
    foreach(explode($delimiter, $string) as $line) {
      // Check we have a string like x=y, and not a string like =y , xy, or x=y=z.
      if (strpos($line, '=') && count(explode('=', $line)) === 2) {
        $parts = explode('=', $line);
        $key = Html::escape($parts[0]);
        $value = Html::escape($parts[1]);
        $params[$key] = $value;
      }
    }
    return $params;
  }

}