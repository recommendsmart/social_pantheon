<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\FormCondition;

use Drupal\Core\Form\FormState;
use Drupal\if_then_else\core\IfthenelseUtilities;
use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Class defined to process make all fields required action.
 */
class FormCondition extends Condition {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'form_class_condition';
  }

  /**
   * Event subscriber for form condition node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => t('Form Class'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\FormCondition\\FormCondition',
      'library' => 'if_then_else/FormCondition',
      'control_class_name' => 'FormIdControl',
      'entity_info' => $form_entity_info,
      'inputs' => [
        'form_state' => [
          'label' => t('Form State'),
          'description' => t('Form state object.'),
          'sockets' => ['form_state'],
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('Did the condition pass?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Validate Form condition node.
   */
  public function validateNode(NodeValidationEvent $event) {
    $node = $event->node;
    // Make sure that form class is not empty and that it exists.
    if (!array_key_exists('selected_entity', (array) $node->data) || !$node->data->selected_entity) {
      $event->errors[] = t('Select entity / bundle of the form that you want to filter in "@node_name".', ['@node_name' => $node->name]);
    }
    elseif ($node->data->selected_entity->value == 'other_form') {
      $form_class = $node->data->otherFormClass;
      $valid_form = IfthenelseUtilities::validateFormClass($form_class);
      if (!$valid_form) {
        $event->errors[] = t('Form class "@form_class" provided in "@node_name either does not exist or is not an instance of FormInterface.', [
          '@form_class' => $form_class,
          '@node_name' => $node->name,
        ]);
      }
    }
    elseif (!array_key_exists('selected_bundle', (array) $node->data) || !$node->data->selected_bundle) {
      $event->errors[] = t('Select bundle of the form that you want to filter in "@node_name".', ['@node_name' => $node->name]);
    }
  }

  /**
   * Process Form condition node.
   */
  public function process() {
    $form_id_matched = FALSE;
    $data = $this->data;
    /** @var \Drupal\Core\Form\FormState $form_state */
    $form_state = $this->inputs['form_state'];
    $form_id = $form_state->getBuildInfo()['form_id'];

    if (isset($data->selected_entity->value) && $data->selected_entity->value == 'other_form') {
      // It is a form class so get the form id using form class.
      $form_state = new FormState();
      $formId_from_class = \Drupal::formBuilder()->getFormId($data->otherFormClass, $form_state);
      if ($formId_from_class == $form_id) {
        $form_id_matched = TRUE;
      }
    }
    elseif (isset($data->selected_entity->value) && $data->selected_entity->value != 'other_form') {
      // It is content entity so get the form id using it.
      $form_object = $form_state->getFormObject();

      // Checking if form is a entity form.
      if (method_exists($form_object, 'getEntity')) {
        $form_entity_id = $form_state->getFormObject()->getEntity()->getEntityTypeId();
        $form_bundle_id = $form_state->getFormObject()->getEntity()->bundle();

        if ($form_entity_id == $data->selected_entity->value && $form_bundle_id == $data->selected_bundle->value) {
          $form_id_matched = TRUE;
        }
      }
    }
    $this->outputs['success'] = $form_id_matched;
  }

}
