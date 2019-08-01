<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SendEmailAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Class defined to execute send email action node.
 */
class SendEmailAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'send_email_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => t('Send Email'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SendEmailAction\\SendEmailAction',
      'inputs' => [
        'to' => [
          'label' => t('To'),
          'description' => t('To email address'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'from' => [
          'label' => t('From'),
          'description' => t('From email address'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'subject' => [
          'label' => t('Subject'),
          'description' => t('Email subject'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'body' => [
          'label' => t('Body'),
          'description' => t('Email body'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        if (!property_exists($node->data, 'value') || empty($node->data->value)) {
          $event->errors[] = t('Enter the value for  "@node_name".', ['@node_name' => $node->name]);
        }
        else {
          // Validate email.
          $email = trim($node->data->value);
          foreach ($node->outputs->text->connections as $connection) {
            if ($connection->input == 'to' &&  !\Drupal::service('email.validator')->isValid($email)) {
              $event->errors[] = t('Enter valid email for input "To" in "@node_name".', ['@node_name' => $node->name]);

            }
            if ($connection->input == 'from' && !\Drupal::service('email.validator')->isValid($email)) {
              $event->errors[] = t('Enter valid email for input "From" in "@node_name".', ['@node_name' => $node->name]);
            }
          }
        }
      }
    }
  }

  /**
   * Process send email action node.
   */
  public function process() {
    $to = $this->inputs['to'];
    $from = $this->inputs['from'];
    $params['subject'] = $this->inputs['subject'];
    $params['body'] = $this->inputs['body'];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    \Drupal::service('plugin.manager.mail')->mail('if_then_else', 'send_email_if_then_else', $to, $langcode, $params, $from, TRUE);
  }

}
