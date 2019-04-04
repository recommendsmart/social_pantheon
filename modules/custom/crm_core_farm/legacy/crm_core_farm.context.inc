<?php

/**
 * @file
 * Integration with context module.
 */

/**
 * Implements hook_context_plugins().
 */
function crm_core_farm_context_plugins() {
  $plugins = array();
  $plugins['crm_core_farm_context_condition_farm_type'] = array(
    'handler' => array(
      'path' => drupal_get_path('module', 'crm_core_farm') . '/includes/context',
      'file' => 'crm_core_farm_context_condition_farm_type.inc',
      'class' => 'crm_core_farm_context_condition_farm_type',
      'parent' => 'context_condition',
    ),
  );
  return $plugins;
}

/**
 * Implements hook_context_registry().
 */
function crm_core_farm_context_registry() {
  return array(
    'conditions' => array(
      'crm_core_farm_context_condition_farm_type' => array(
        'title' => t('CRM Core Farm Type'),
        'plugin' => 'crm_core_farm_context_condition_farm_type',
      ),
    ),
  );
}

/**
 * Implements hook_context_page_condition().
 */
function crm_core_farm_context_page_condition() {
  if ($plugin = context_get_plugin('condition', 'crm_core_farm_context_condition_farm_type')) {
    $farm = menu_get_object('crm_core_farm', 2);
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
    if (strpos($_GET['q'], 'crm-core/farm/add') !== FALSE) {
      $farm = entity_create('crm_core_farm', array('type' => arg(3)));
      $op = 'edit';
    }

    if ($op) {
      $plugin->execute($farm, $op);
    }
  }
}
