<?php

/**
 * @file
 * Integration with context module.
 */

/**
 * Implements hook_context_plugins().
 */
function crm_core_data_context_plugins() {
  $plugins = array();
  $plugins['crm_core_data_context_condition_data_type'] = array(
    'handler' => array(
      'path' => drupal_get_path('module', 'crm_core_data') . '/includes/context',
      'file' => 'crm_core_data_context_condition_data_type.inc',
      'class' => 'crm_core_data_context_condition_data_type',
      'parent' => 'context_condition',
    ),
  );
  return $plugins;
}

/**
 * Implements hook_context_registry().
 */
function crm_core_data_context_registry() {
  return array(
    'conditions' => array(
      'crm_core_data_context_condition_data_type' => array(
        'title' => t('CRM Core Data Type'),
        'plugin' => 'crm_core_data_context_condition_data_type',
      ),
    ),
  );
}

/**
 * Implements hook_context_page_condition().
 */
function crm_core_data_context_page_condition() {
  if ($plugin = context_get_plugin('condition', 'crm_core_data_context_condition_data_type')) {
    $data = menu_get_object('crm_core_data', 2);
    $op = '';
    if ($data) {
      switch (arg(3)) {
        case '':
          $op = 'view';
          break;

        case 'edit':
          $op = 'form';
          break;
      }
    }
    // Also execute context on data add page.
    if (strpos($_GET['q'], 'crm-core/data/add') !== FALSE) {
      $data = entity_create('crm_core_data', array('type' => arg(3)));
      $op = 'edit';
    }

    if ($op) {
      $plugin->execute($data, $op);
    }
  }
}
