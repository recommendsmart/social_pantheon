<?php

namespace Drupal\if_then_else\core\Nodes\Actions\BooleanNotAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;


/**
 * Class defined to execute subtract numbers action node.
 */
class BooleanNotAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'boolean_not_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => t('Boolean NOT'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\BooleanNotAction\\BooleanNotAction',
      'inputs' => [
        'input1' => [
          'label' => t('Input'),
          'description' => t('Input'),
          'sockets' => ['bool'],
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => t('Output'),
          'description' => t('NOT of the input'),
          'socket' => 'bool'
        ],
      ]
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function process() {
    $this->outputs['success'] = !$this->inputs['input'];
  }
}
