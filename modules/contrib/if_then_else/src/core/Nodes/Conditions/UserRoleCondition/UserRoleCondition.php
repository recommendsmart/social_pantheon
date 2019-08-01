<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UserRoleCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

class UserRoleCondition extends Condition {

  /**
   * {@inheritDoc}
   */
  public static function getName() {
    return 'user_role_condition';
  }

  /**
   * {@inheritDoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    $role_array = [];
    foreach ($roles as $rid => $role) {
      $role_array[$rid] = $role->label();
    }

    $event->nodes[static::getName()] = [
      'label' => t('User Role'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UserRoleCondition\\UserRoleCondition',
      'library' => 'if_then_else/UserRoleCondition',
      'control_class_name' => 'UserRoleConditionControl',
      'roles' => $role_array,
      'inputs' => [
        'user' => [
          'label' => t('User'),
          'description' => t('User object.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ],
        'roles' => [
          'label' => t('Roles'),
          'description' => t('Roles to check for. Can be a comma-separated string of role ids or an array of role ids.'),
          'sockets' => ['string', 'array'],
        ]
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('Does the user have role(s)?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    $inputs = $event->node->inputs;
    if ($data->input_selection != 'list' && !sizeof($inputs->roles->connections)) {
      $event->errors[] = t('Provide roles that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->input_selection == 'list' && !sizeof($data->selected_roles)) {
      $event->errors[] = t('Select roles that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  public function process() {
    $match = $this->data->match->type;

    $roles_to_check = [];
    if ($this->data->input_selection == 'list') {
      foreach ($this->data->selected_roles as $role) {
        $roles_to_check[] = $role->name;
      }
    }
    elseif (is_string($this->inputs['roles'])) {
      foreach (explode(',', $this->inputs['roles']) as $role) {
        $roles_to_check[] = trim($role);
      }
    }
    elseif (is_array($this->inputs['roles'])) {
      $roles_to_check[] = $this->inputs['roles'];
    }

    /** @var \Drupal\user\Entity\User $user */
    $user = $this->inputs['user'];

    if ($match == 'any') {
      foreach ($roles_to_check as $rid) {
        if ($user->hasRole($rid)) {
          $this->setSuccess(TRUE);
          return;
        }
      }
      $this->setSuccess(FALSE);
      return;
    }
    elseif ($match == 'all') {
      foreach ($roles_to_check as $rid) {
        if (!$user->hasRole($rid)) {
          $this->setSuccess(FALSE);
          return;
        }
      }
      $this->setSuccess(TRUE);
      return;
    }
  }
}