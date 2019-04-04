<?php

/**
 * @file
 * Class for determining if the user is in the right farm context.
 */

use Drupal\crm_core_farm\Entity\FarmType;

/**
 * Trigger context on node view only.
 */
define('CONTEXT_CRM_FARM_VIEW', 0);

/**
 * Trigger context on node view and node form.
 */
define('CONTEXT_CRM_FARM_NODE_FORM', 1);

/**
 * Trigger context on node form only.
 */
define('CONTEXT_CRM_FARM_NODE_FORM_ONLY', 2);

/**
 * Expose node views/node forms of specific node types as a context condition.
 */
class crm_core_farm_context_condition_farm_type extends context_condition {

  function condition_values() {
    $values = array();
    foreach (FarmType::loadMultiple() as $type) {
      $values[$type->type] = check_plain($type->name);
    }
    return $values;
  }

  function options_form($context) {
    $defaults = $this->fetch_from_context($context, 'options');
    return array(
      'farm_form' => array(
        '#title' => t('Set on farm form'),
        '#type' => 'select',
        '#options' => array(
           CONTEXT_CRM_FARM_VIEW => t('No'),
           CONTEXT_CRM_FARM_NODE_FORM => t('Yes'),
           CONTEXT_CRM_FARM_NODE_FORM_ONLY => t('Only on farm form')
        ),
        '#description' => t('Set this context on farm forms'),
        '#default_value' => isset($defaults['farm_form']) ? $defaults['farm_form'] : TRUE,
      ),
    );
  }

  function execute($farm, $op) {
    foreach ($this->get_contexts($farm->type) as $context) {

      $options = $this->fetch_from_context($context, 'options');
      if ($op === 'form') {
        $options = $this->fetch_from_context($context, 'options');
        if (!empty($options['farm_form']) && in_array($options['farm_form'], array(CONTEXT_CRM_FARM_NODE_FORM, CONTEXT_CRM_FARM_NODE_FORM_ONLY))) {
          $this->condition_met($context, $farm->type);
        }
      }
      elseif (empty($options['farm_form']) || $options['farm_form'] != CONTEXT_CRM_FARM_NODE_FORM_ONLY) {
        $this->condition_met($context, $farm->type);
      }
    }
  }
}
