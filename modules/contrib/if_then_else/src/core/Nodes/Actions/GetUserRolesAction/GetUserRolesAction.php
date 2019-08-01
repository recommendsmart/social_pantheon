<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetUserRolesAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Class GetUserRolesAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\GetUserRolesAction
 */
class GetUserRolesAction extends Action {

  /**
   * {@inheritDoc}
   */
  public static function getName() {
    return 'set_cookie_action';
  }

  /**
   * {@inheritDoc}.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Get User Roles'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetUserRolesAction\\GetUserRolesAction',
      'inputs' => [
        'user' => [
          'label' => t('User'),
          'description' => t('Account object.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ]
      ],
      'outputs' => [
        'roles' => [
          'label' => t('Roles'),
          'description' => t('Array of roles that the user has.'),
          'socket' => 'array'
        ]
      ]
    ];
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->inputs['user'];
    $this->outputs['roles'] = $user->getRoles();
  }
}
