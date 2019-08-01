<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SendAccountEmailAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Send account email action class.
 */
class SendAccountEmailAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'send_account_email_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Send Account Email'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SendAccountEmailAction\\SendAccountEmailAction',
      'library' => 'if_then_else/SendAccountEmailAction',
      'control_class_name' => 'SendAccountEmailActionControl',
      'compare_options' => [
        ['code' => 'register_admin_created', 'name' => 'Welcome message for user created by the admin'],
        ['code' => 'register_no_approval_required', 'name' => 'Welcome message when user self-registers'],
        ['code' => 'register_pending_approval', 'name' => 'Welcome message, user pending admin approval'],
        ['code' => 'password_reset', 'name' => 'Password recovery request'],
        ['code' => 'status_activated', 'name' => 'Account activated'],
        ['code' => 'status_blocked', 'name' => 'Account blocked'],
        ['code' => 'cancel_confirm', 'name' => 'Account cancellation request'],
        ['code' => 'status_canceled', 'name' => 'Account canceled'],
      ],
      'inputs' => [
        'user' => [
          'label' => t('User Id / User object'),
          'description' => t('User Id or User Object.'),
          'sockets' => ['number', 'object.entity.user'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_type->code)) {
      $event->errors[] = t('Select type of email to be sent in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $user = $this->inputs['user'];
    $email_type = $this->data->selected_type->code;
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

    _user_mail_notify($email_type, $user);

  }

}
