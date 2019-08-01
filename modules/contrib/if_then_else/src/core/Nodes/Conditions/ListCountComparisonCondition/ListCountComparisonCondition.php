<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\ListCountComparisonCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * List count comparison condition class.
 */
class ListCountComparisonCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'list_count_comparison_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('List Count Comparison'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\ListCountComparisonCondition\\ListCountComparisonCondition',
      'library' => 'if_then_else/ListCountComparisonCondition',
      'control_class_name' => 'ListCountComparisonConditionControl',
      'operator_options' => [
        ['code' => 'equal', 'name' => '=='],
        ['code' => 'less_than', 'name' => '<'],
        ['code' => 'greater_than', 'name' => '>'],
      ],
      'inputs' => [
        'list' => [
          'label' => t('List'),
          'description' => t('The list to compare the value to.'),
          'sockets' => ['array'],
          'required' => TRUE,
        ],
        'value' => [
          'label' => t('Value'),
          'description' => t('The value of that the count is to compare to.'),
          'sockets' => ['number'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the comparison returns true'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that data type option is not empty.
    if (empty($event->node->data->operator)) {
      $event->errors[] = t('Select at least one operator in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'number_value') {
        // To check empty input.
        foreach ($node->outputs->number->connections as $connection) {
          if ($connection->input == 'value' &&  (!property_exists($node->data, 'value') || !is_numeric($node->data->value))) {
            $event->errors[] = t('Enter count value in "@node_name".', ['@node_name' => $node->name]);

          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $list = $this->inputs['list'];
    $value = $this->inputs['value'];

    $operator = $this->data->operator[0]->code;

    $output = FALSE;

    switch ($operator) {
      case 'equal':
        $output = count($list) == $value;
        break;

      case 'less_than':
        $output = count($list) < $value;
        break;

      case 'greater_than':
        $output = count($list) > $value;
        break;
    }

    $this->outputs['success'] = $output;
  }

}
