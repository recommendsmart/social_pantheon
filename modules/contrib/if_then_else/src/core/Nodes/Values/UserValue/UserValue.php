<?php

namespace Drupal\if_then_else\core\Nodes\Values\UserValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\user\Entity\User;

/**
 * Textvalue node class.
 */
class UserValue extends Value {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'user_value';
  }

  /**
   * {@inheritDoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Logged-In User'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\UserValue\\UserValue',
      'outputs' => [
        'user' => [
          'label' => t('User'),
          'description' => t('User object.'),
          'socket' => 'object.entity.user',
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function process() {
    $this->outputs['user'] = User::load(\Drupal::currentUser()->id());
  }
}
