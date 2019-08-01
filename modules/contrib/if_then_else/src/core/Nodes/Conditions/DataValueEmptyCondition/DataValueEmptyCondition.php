<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\DataValueEmptyCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Data value is empty condition class.
 */
class DataValueEmptyCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'data_value_empty_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Data Value Empty'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\DataValueEmptyCondition\\DataValueEmptyCondition',
      'inputs' => [
        'input' => [
          'label' => t('Data to check'),
          'description' => t('Data to check.'),
          'sockets' => [
            'string',
            'number',
            'array',
            'object.entity.user',
            'object.entity.node',
          ],
          'required' => TRUE,
        ],
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
   * {@inheritdoc}
   */
  public function process() {

    $input = $this->inputs['input'];
    $output = FALSE;

    if (empty($input)) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
