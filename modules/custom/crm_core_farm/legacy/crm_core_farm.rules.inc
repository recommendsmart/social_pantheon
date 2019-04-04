<?php

/**
 * @file
 * Rules integration for CRM Core Farm.
 */


/**
 * Implements hook_rules_event_info().
 */
function crm_core_farm_rules_event_info() {
  $events = array();
  $group = t('CRM Core farm');

  $events['crm_core_farm_update'] = array(
    'label' => t('Farm has been updated'),
    'group' => $group,
    'variables' => crm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  $events['crm_core_farm_insert'] = array(
    'label' => t('Farm has been created'),
    'group' => $group,
    'variables' => crm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  $events['crm_core_farm_delete'] = array(
    'label' => t('Farm has been deleted'),
    'group' => $group,
    'variables' => crm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  return $events;
}


/**
 * Returns a variables array for CRM events.
 */
function crm_core_farm_rules_event_variables() {
  $variables = array(
    'crm_core_farm' => array(
      'type' => 'crm_core_farm',
      'label' => t('CRM Core Farm'),
    ),
  );

  return $variables;
}
