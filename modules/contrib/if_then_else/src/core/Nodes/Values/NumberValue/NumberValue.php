<?php

namespace Drupal\if_then_else\core\Nodes\Values\NumberValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Number value node class.
 */
class NumberValue extends Value {

  /**
   * Return name of number value node.
   */
  public static function getName() {
    return 'number_value';
  }

  /**
   * Event subscriber for registering number value node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Number'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\NumberValue\\NumberValue',
      'library' => 'if_then_else/NumberValue',
      'control_class_name' => 'NumberValueControl',
      'outputs' => [
        'number' => [
          'label' => t('Output'),
          'description' => t('Output number.'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * Validate node.
   */
  public function validateNode(NodeValidationEvent $event) {
  }

  /**
   * Process function for number value node.
   */
  public function process() {
    $this->outputs['number'] = $this->data->value;
  }

}
