<?php

namespace Drupal\if_then_else\core\Nodes\Events\UserLoginEvent;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * User login event node class.
 */
class UserLoginEvent extends Event {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'user_login_event';
  }

  /**
   * Event subscriber for user login event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('User Login'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\UserLoginEvent\\UserLoginEvent',
      'outputs' => [
        'user' => [
          'label' => t('User'),
          'description' => t('User object.'),
          'socket' => 'object.entity.user',
        ],
      ],
    ];
  }

}
