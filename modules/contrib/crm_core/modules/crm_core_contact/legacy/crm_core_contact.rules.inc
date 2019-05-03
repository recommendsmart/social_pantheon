<?php

/**
 * @file
 * Rules integration for CRM Core Contact.
 *
 * @TODO: Unported.
 */

//@codingStandardsIgnoreFile

/**
 * Implements hook_rules_event_info().
 */
function crm_core_contact_rules_event_info() {
  $events = [];
  $group = t('CRM Core Contact');

  $events['crm_core_contact_update'] = [
    'label' => t('Contact has been updated'),
    'group' => $group,
    'variables' => crm_core_contact_rules_event_variables(),
    'access callback' => '',
  ];

  $events['crm_core_contact_insert'] = [
    'label' => t('Contact has been created'),
    'group' => $group,
    'variables' => crm_core_contact_rules_event_variables(),
    'access callback' => '',
  ];

  $events['crm_core_contact_delete'] = [
    'label' => t('Contact has been deleted'),
    'group' => $group,
    'variables' => crm_core_contact_rules_event_variables(),
    'access callback' => '',
  ];

  return $events;
}

/**
 * Returns a variables array for CRM events.
 */
function crm_core_contact_rules_event_variables() {
  $variables = [
    'crm_core_contact' => [
      'type' => 'crm_core_contact',
      'label' => t('CRM Core Contact'),
    ],
  ];

  return $variables;
}
