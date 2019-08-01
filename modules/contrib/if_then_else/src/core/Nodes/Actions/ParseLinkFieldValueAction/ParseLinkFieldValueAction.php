<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseLinkFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Parse Link field value node class.
 */
class ParseLinkFieldValueAction extends Action {

  /**
   * Return name of Link field value node.
   */
  public static function getName() {
    return 'link_field_value_action';
  }

  /**
   * Event subscriber for registering Link field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Parse Link Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseLinkFieldValueAction\\ParseLinkFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => t('Link field object'),
          'description' => t('Link field object'),
          'sockets' => ['object.field.link'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'uri' => [
          'label' => t('Link Uri'),
          'description' => t('Link Uri'),
          'socket' => 'string',
        ],
        'title' => [
          'label' => t('Link title'),
          'description' => t('Link Title'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Link field value action node.
   */
  public function process() {
    $this->outputs['uri'] = $this->inputs['field_value']->uri;
    $this->outputs['title'] = $this->inputs['field_value']->title;
  }

}
