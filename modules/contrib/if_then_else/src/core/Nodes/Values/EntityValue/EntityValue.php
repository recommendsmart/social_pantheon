<?php

namespace Drupal\if_then_else\core\Nodes\Values\EntityValue;

use Drupal\if_then_else\core\Nodes\Values\Value;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Component\Utility\Html;
/**
 * Textvalue node class.
 */
class EntityValue extends Value {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'entity_value';
  }

  /**
   * Event subscriber of registering node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Entity'),
      'type' => 'value',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Values\\EntityValue\\EntityValue',
      'library' => 'if_then_else/EntityValue',
      'control_class_name' => 'EntityValueControl',
      'component_class_name' => 'EntityValueComponent',
      'inputs' => [
        'data' => [
          'label' => t('Entity Id'),
          'description' => t('Take Entity Id input'),
          'sockets' => ['number'],
        ]
      ] ,   
      'outputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity Object'),
          'socket' => 'object.entity',
        ],
      ],
    ];
  }

  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if(!property_exists($data,'selected_entity')){
      $event->errors[] = t('Select a Entity type in "@node_name".', ['@node_name' => $event->node->name]);
    }

    if (!property_exists($data, 'input_selection')) {
      $event->errors[] = t('Provide an entity id in "@node_name".', ['@node_name' => $event->node->name]);
    }

    $inputs = $event->node->inputs;
    if ($data->input_selection == 'value' && (!property_exists($data, 'entityId') || empty(trim($data->entityId)))) {
      $event->errors[] = t('Provide an entity id in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->input_selection == 'input' && !sizeof($inputs->data->connections)) {
      $event->errors[] = t('Provide an entity id in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process function for Textvalue node.
   */
  public function process() {

    if ($this->data->input_selection == 'value') {
      $entity_id = Html::escape($this->data->entityId);
    }
    else {
      $entity_id = $this->inputs['data'];
    }
    $entity = $this->data->selected_entity->value;
    
    // Using the storage controller.
    $entity_object = \Drupal::entityTypeManager()->getStorage($entity)->load($entity_id);
    $this->outputs['entity'] = $entity_object;
  }

}
