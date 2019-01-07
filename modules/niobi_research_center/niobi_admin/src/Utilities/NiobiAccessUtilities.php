<?php
/**
 * @file
 * Contains \Drupal\niobi_admin\Utilities\NiobiAccessUtilities.
 */
namespace Drupal\niobi_admin\Utilities;

/**
 * Class NiobiAccessUtilities
 * @package Drupal\niobi_admin\Utilities
 */
class NiobiAccessUtilities {

  public static function get_access_plugins() {
    $plugin_manager = \Drupal::service('plugin.manager.niobi_admin.niobi_access');
    $plugin_definitions = $plugin_manager->getDefinitions();

    return $plugin_definitions;
  }

  public function determine_access($entity, $account) {
    $plugin_manager = \Drupal::service('plugin.manager.niobi_admin.niobi_access');
    $plugin_definitions = $plugin_manager->getDefinitions();

    return TRUE;
  }
}
