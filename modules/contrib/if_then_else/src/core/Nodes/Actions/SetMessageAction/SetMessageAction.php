<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetMessageAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Class defined to execute set message action node.
 */
class SetMessageAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'set_message_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Set Message'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetMessageAction\\SetMessageAction',
      'library' => 'if_then_else/SetMessageAction',
      'control_class_name' => 'SetMessageActionControl',
      'severity_options' => ['status', 'warning', 'error'],
      'inputs' => [
        'message' => [
          'label' => t('Message'),
          'description' => t('Message string'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation for make fields required action node.
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that severity option is selected.
    if (!property_exists($event->node->data, "selected_options")) {
      $event->errors[] = t('Select at least one severity in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process set message action node.
   */
  public function process() {
    if (isset($this->data->selected_options->label) && !empty($this->inputs['message'])) {
      $set_message_text = $this->inputs['message'];
      $severity = $this->data->selected_options->label;
      \Drupal::messenger()->addMessage(t($set_message_text), $severity);
    }
    else {
      $this->setSuccess(FALSE);
      return;
    }
  }
}
