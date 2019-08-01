<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseTextLongFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Parse Text Long field value action node class.
 */
class ParseTextLongFieldValueAction extends Action {

  /**
   * Return name of Text long field value action node.
   */
  public static function getName() {
    return 'text_long_field_value_action';
  }

  /**
   * Event subscriber for registering Text long field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Parse Text Long Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseTextLongFieldValueAction\\ParseTextLongFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => t('Text Long field object'),
          'description' => t('Text Long field object'),
          'sockets' => ['object.field.text_long'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'value' => [
          'label' => t('Text Field Value'),
          'description' => t('Text Field Value'),
          'socket' => 'string',
        ],
        'format' => [
          'label' => t('Text Field Format'),
          'description' => t('Text Field Format'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Text long field value action node.
   */
  public function process() {
    $this->outputs['value'] = $this->inputs['field_value']->value;
    $this->outputs['format'] = $this->inputs['field_value']->format;
  }

}
