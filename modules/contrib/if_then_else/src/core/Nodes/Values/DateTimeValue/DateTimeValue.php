<?php

namespace Drupal\if_then_else\core\Nodes\Values\DateTimeValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Textvalue node class.
 */
class DateTimeValue extends Value {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'date_time_value';
  }

  /**
   * Event subscriber of registering node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Date Time'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\DateTimeValue\\DateTimeValue',
      'library' => 'if_then_else/DateTimeValue',
      'control_class_name' => 'DateTimeValueControl',
      'outputs' => [
        'datetime' => [
          'label' => t('Date Time'),
          'description' => t('Date Time String'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // $data = $event->node->data;.
  }

  /**
   * Process function for Textvalue node.
   */
  public function process() {

    $date = strtotime($this->data->value);

    $date = \Drupal::service('date.formatter')->format($date, 'custom', 'Y-m-d H:i:s', drupal_get_user_timezone());
    $date = str_replace(' ','T', trim($date));
    
    // Using the storage controller.
    $this->outputs['datetime'] = $date;
  }

}
