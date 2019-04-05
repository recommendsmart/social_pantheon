<?php

/**
 * @file
 * Integration with context module.
 */

/**
 * Implements hook_context_plugins().
 */
function drm_core_farm_context_plugins() {
  $plugins = array();
  $plugins['drm_core_farm_context_condition_farm_type'] = array(
    'handler' => array(
      'path' => drupal_get_path('module', 'drm_core_farm') . '/includes/context',
      'file' => 'drm_core_farm_context_condition_farm_type.inc',
      'class' => 'drm_core_farm_context_condition_farm_type',
      'parent' => 'context_condition',
    ),
  );
  return $plugins;
}

/**
 * Implements hook_context_registry().
 */
function drm_core_farm_context_registry() {
  return array(
    'conditions' => array(
      'drm_core_farm_context_condition_farm_type' => array(
        'title' => t('DRM Core Farm Type'),
        'plugin' => 'drm_core_farm_context_condition_farm_type',
      ),
    ),
  );
}

/**
 * Implements hook_context_page_condition().
 */
function drm_core_farm_context_page_condition() {
  if ($plugin = context_get_plugin('condition', 'drm_core_farm_context_condition_farm_type')) {
    $farm = menu_get_object('drm_core_farm', 2);
    $op = '';
    if ($farm) {
      switch (arg(3)) {
        case '':
          $op = 'view';
          break;

        case 'edit':
          $op = 'form';
          break;
      }
    }
    // Also execute context on farm add page.
    if (strpos($_GET['q'], 'drm-core/farm/add') !== FALSE) {
      $farm = entity_create('drm_core_farm', array('type' => arg(3)));
      $op = 'edit';
    }

    if ($op) {
      $plugin->execute($farm, $op);
    }
  }
}
