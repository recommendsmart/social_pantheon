<?php

namespace Drupal\if_then_else\core\Nodes\Actions\BlockUserAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Block a user action node class.
 */
class BlockUserAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'block_user_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Block User'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\BlockUserAction\\BlockUserAction',
      'inputs' => [
        'user' => [
          'label' => t('User Id / User object'),
          'description' => t('User Id or User object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {

    $user = $this->inputs['user'];

    if (is_numeric($user)) {
      $user = User::load($user);
      if (empty($user)) {
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$user instanceof UserInterface) {
      $this->setSuccess(FALSE);
      return;
    }

    $user->block();
    $user->save();

  }

}
