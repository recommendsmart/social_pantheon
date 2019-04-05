<?php

/**
 * @file
 * Rules integration for DRM Core Farm.
 */


/**
 * Implements hook_rules_event_info().
 */
function drm_core_farm_rules_event_info() {
  $events = array();
  $group = t('DRM Core farm');

  $events['drm_core_farm_update'] = array(
    'label' => t('Farm has been updated'),
    'group' => $group,
    'variables' => drm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  $events['drm_core_farm_insert'] = array(
    'label' => t('Farm has been created'),
    'group' => $group,
    'variables' => drm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  $events['drm_core_farm_delete'] = array(
    'label' => t('Farm has been deleted'),
    'group' => $group,
    'variables' => drm_core_farm_rules_event_variables(),
    'access callback' => '',
  );

  return $events;
}


/**
 * Returns a variables array for DRM events.
 */
function drm_core_farm_rules_event_variables() {
  $variables = array(
    'drm_core_farm' => array(
      'type' => 'drm_core_farm',
      'label' => t('DRM Core Farm'),
    ),
  );

  return $variables;
}
