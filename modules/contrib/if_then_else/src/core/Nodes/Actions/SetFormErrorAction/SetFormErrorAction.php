<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetFormErrorAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Class defined to set form error action node.
 */
class SetFormErrorAction extends Action {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'set_form_error_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $form_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);

    $event->nodes[static::getName()] = [
      'label' => t('Set Form Error'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetFormErrorAction\\SetFormErrorAction',
      'library' => 'if_then_else/SetFormErrorAction',
      'control_class_name' => 'SetFormErrorActionControl',
      'form_fields' => $form_fields,
      'inputs' => [
        'form_state' => [
          'label' => t('Form State'),
          'description' => t('Form state object.'),
          'sockets' => ['form_state'],
          'required' => TRUE,
        ],
        'message' => [
          'label' => t('Error message'),
          'description' => t('Message for form state.'),
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
    // Make sure that form_fields array is not empty.
    if (!count($event->node->data->form_fields)) {
      $event->errors[] = t('Select at least one field in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;

    foreach ($nodes as $node) {
      if ($node->data->type == 'event' && $node->data->name != 'form_validate_event') {
        $event->errors[] = t('Set Form Error will only work with Form validate Event');
      }
    }
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {

    $form_state = $this->inputs['form_state'];
    $message = $this->inputs['message'];
    $form_fields = $this->data->form_fields;

    $form_state->setErrorByName($form_fields[0]->code, $message);

  }

}
