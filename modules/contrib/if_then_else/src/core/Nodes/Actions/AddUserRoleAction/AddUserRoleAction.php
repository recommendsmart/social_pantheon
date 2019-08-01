<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddUserRoleAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Add user role action class.
 */
class AddUserRoleAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'add_user_role_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    $role_array = [];
    foreach ($roles as $rid => $role) {
      $role_array[$rid] = $role->label();
    }
    $event->nodes[static::getName()] = [
      'label' => t('Add User Roles'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddUserRoleAction\\AddUserRoleAction',
      'library' => 'if_then_else/AddUserRoleAction',
      'control_class_name' => 'AddUserRoleActionControl',
      'roles' => $role_array,
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
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that role option is not empty.
    if (empty($event->node->data->selected_options)) {
      $event->errors[] = t('Select at least one role in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $roles = $this->data->selected_options;
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

    foreach ($roles as $role) {
      if (!$user->hasRole($role->name)) {
        $user->addRole($role->name);
      }
      else {
        \Drupal::logger('if_then_else')->notice(t("Rule @node_name did not run as the user already have the role @role", ['@node_name' => $this->data->name, '@role' => $role->name]));
      }
    }
    $user->save();

  }

}
