<?php

/**
 * @file
 * Rules integration for CRM Core Data.
 */


/**
 * Implements hook_rules_event_info().
 */
function crm_core_data_rules_event_info() {
  $events = array();
  $group = t('CRM Core data');

  $events['crm_core_data_update'] = array(
    'label' => t('Data has been updated'),
    'group' => $group,
    'variables' => crm_core_data_rules_event_variables(),
    'access callback' => '',
  );

  $events['crm_core_data_insert'] = array(
    'label' => t('Data has been created'),
    'group' => $group,
    'variables' => crm_core_data_rules_event_variables(),
    'access callback' => '',
  );

  $events['crm_core_data_delete'] = array(
    'label' => t('Data has been deleted'),
    'group' => $group,
    'variables' => crm_core_data_rules_event_variables(),
    'access callback' => '',
  );

  return $events;
}


/**
 * Returns a variables array for CRM events.
 */
function crm_core_data_rules_event_variables() {
  $variables = array(
    'crm_core_data' => array(
      'type' => 'crm_core_data',
      'label' => t('CRM Core Data'),
    ),
  );

  return $variables;
}
