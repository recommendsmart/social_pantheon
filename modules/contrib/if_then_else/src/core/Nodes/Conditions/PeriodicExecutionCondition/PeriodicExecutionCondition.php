<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\PeriodicExecutionCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Periodic execution condition class.
 */
class PeriodicExecutionCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'periodic_execution_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Periodic Execution'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\PeriodicExecutionCondition\\PeriodicExecutionCondition',
      'library' => 'if_then_else/PeriodicExecutionCondition',
      'control_class_name' => 'PeriodicExecutionConditionControl',
      'compare_options' => [
        ['code' => '1', 'name' => '1'],
        ['code' => '2', 'name' => '2'],
        ['code' => '4', 'name' => '4'],
        ['code' => '8', 'name' => '8'],
        ['code' => '12', 'name' => '12'],
        ['code' => '24', 'name' => '24'],
        ['code' => '48', 'name' => '48'],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('Data value is empty?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->form_selection)) {
      $event->errors[] = t('Select run every option in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }
    if ($data->form_selection == 'list' && empty($data->selected_option)) {
      $event->errors[] = t('Select an hour in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->form_selection == 'other') {
      if (empty($data->valueText)) {
        $event->errors[] = t('Please enter value for custom in "@node_name".', ['@node_name' => $event->node->name]);
      }
      elseif (!is_numeric($data->valueText)) {
        $event->errors[] = t('Please enter numeric value for hour in "@node_name".', ['@node_name' => $event->node->name]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $data = $this->data;
    $run_every_hour = 0;
    if ($data->form_selection == 'list') {
      $run_every_hour = $data->selected_option->code;
    }
    elseif ($data->form_selection == 'other') {
      $run_every_hour = $data->valueText;
    }
    elseif (($data->compare_type[0]->code == 'custom')  && !empty($data->valueText)) {
      $run_every_hour = $data->valueText;
    }
    $last_rule_run = \Drupal::configFactory()->getEditable('if_then_else.settings')->get(self::getName());
    $last_rule_run = !empty($last_rule_run) ? $last_rule_run : 0;
    $current_time = time();
    $output = FALSE;
    $seconds = $current_time - $last_rule_run;
    $last_run_hours = $seconds / 60 / 60;
    if ($last_run_hours >= $run_every_hour || empty($last_rule_run)) {
      $output = TRUE;
      \Drupal::configFactory()->getEditable('if_then_else.settings')
        ->set(self::getName(), $current_time)
        ->save();
    }

    $this->outputs['success'] = $output;

  }

}
