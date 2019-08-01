<?php

namespace Drupal\if_then_else\core\Nodes\Events\UserLogoutEvent;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * User logout event node class.
 */
class UserLogoutEvent extends Event {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'user_logout_event';
  }

  /**
   * Event subscriber for user logout event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('User Logout'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\UserLogoutEvent\\UserLogoutEvent',
      'outputs' => [
        'user' => [
          'label' => t('user'),
          'description' => t('User object.'),
          'socket' => 'object.entity.user',
        ],
      ],
    ];
  }

}
