<?php

/**
 * @file
 * Class for determining if the user is in the right data context.
 */

use Drupal\crm_core_data\Entity\DataType;

/**
 * Trigger context on node view only.
 */
define('CONTEXT_CRM_DATA_VIEW', 0);

/**
 * Trigger context on node view and node form.
 */
define('CONTEXT_CRM_DATA_NODE_FORM', 1);

/**
 * Trigger context on node form only.
 */
define('CONTEXT_CRM_DATA_NODE_FORM_ONLY', 2);

/**
 * Expose node views/node forms of specific node types as a context condition.
 */
class crm_core_data_context_condition_data_type extends context_condition {

  function condition_values() {
    $values = array();
    foreach (DataType::loadMultiple() as $type) {
      $values[$type->type] = check_plain($type->name);
    }
    return $values;
  }

  function options_form($context) {
    $defaults = $this->fetch_from_context($context, 'options');
    return array(
      'data_form' => array(
        '#title' => t('Set on data form'),
        '#type' => 'select',
        '#options' => array(
           CONTEXT_CRM_DATA_VIEW => t('No'),
           CONTEXT_CRM_DATA_NODE_FORM => t('Yes'),
           CONTEXT_CRM_DATA_NODE_FORM_ONLY => t('Only on data form')
        ),
        '#description' => t('Set this context on data forms'),
        '#default_value' => isset($defaults['data_form']) ? $defaults['data_form'] : TRUE,
      ),
    );
  }

  function execute($data, $op) {
    foreach ($this->get_contexts($data->type) as $context) {

      $options = $this->fetch_from_context($context, 'options');
      if ($op === 'form') {
        $options = $this->fetch_from_context($context, 'options');
        if (!empty($options['data_form']) && in_array($options['data_form'], array(CONTEXT_CRM_DATA_NODE_FORM, CONTEXT_CRM_DATA_NODE_FORM_ONLY))) {
          $this->condition_met($context, $data->type);
        }
      }
      elseif (empty($options['data_form']) || $options['data_form'] != CONTEXT_CRM_DATA_NODE_FORM_ONLY) {
        $this->condition_met($context, $data->type);
      }
    }
  }
}
