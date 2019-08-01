<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetEntityFieldValue;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use stdClass;

/**
 * Class defined to set value of entity field.
 */
class SetEntityFieldValue extends Action {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'set_entity_field_value_action';
  }

  /**
   * Event subscriber for register set entit field value.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $form_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);
    $field_entity = $if_then_else_utilities->getEntityByFieldName($form_fields);
    $fields_type = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info, 'field_type');

    $event->nodes[static::getName()] = [
      'label' => t('Set Entity Field Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetEntityFieldValue\\SetEntityFieldValue',
      'library' => 'if_then_else/SetEntityFieldValue',
      'control_class_name' => 'SetEntityFieldValueControl',
      'form_fields' => $form_fields,
      'form_fields_type' => $fields_type,
      'field_entity_bundle' => $field_entity,
      //'component_class_name' => 'SetEntityFieldValueActionComponent',
      'inputs' => [
        'field_value' => [
          'label' => t('Field Value'),
          'description' => t('Field value to set.'),
          'sockets' => ['string', 'string.url', 'bool', 'number', 'object.field.text_with_summary', 'object.field.image', 'object.field.link', 'object.field.text_long'],
          'required' => TRUE,
        ],
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object'),
          'sockets' => ['object.entity','object.entity.node','object.entity.user'],
          'required' => TRUE,
        ]
      ],
      'outputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object'),
          'socket' => 'object.entity',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->form_fields)) {
      $event->errors[] = t('Select a field name to fetch it\' value in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process function to fetch value of field.
   */
  public function process() {
    $this->setValueOfField();
  }

  /**
   * Set field value.
   */
  private function setValueOfField() {
    $field_value = $this->inputs['field_value'];
    $entity = $this->inputs['entity'];
    $field_name = $this->data->form_fields->code;
    $field_type = $this->data->field_type;

    if($field_name == 'title'){
      if($entity->getEntityTypeId() == 'node'){
        $entity->setTitle($field_value);
      }else{
        $entity->setLabel($field_value);
      }
    }else{
      switch ($field_type){
        case 'list_string':
        case 'string':
        case 'list_integer':
        case 'email':        
        case 'list_float':
        case 'list_integer':
        case 'decimal':
        case 'float':
        case 'integer':
        case 'string_long':
        case 'boolean':        
          $entity->set($field_name, $field_value);
          break;

        case 'datetime':
          // /$entity->set($field_name,date('c', $field_value->getPhpDateTime()->getTimestamp()));
          break;

        case 'text_with_summary':
          $entity->{$field_name}->setValue(['value' => $field_value->value, 'format' => $field_value->format, 'summary' => $field_value->summary]);
          break;
        
        case 'text_long':
        case 'text':
          $entity->{$field_name}->setValue(['value' => $field_value->value, 'format' => $field_value->format]);
          break;
        
        case 'entity_reference':
          $entity->{$field_name}->target_id = $field_value;
          break;

        case 'link':
        $entity->{$field_name}->setValue(['uri' => $field_value->uri, 'title' => $field_value->title]);
          break;

      }
    }
    
    $this->outputs['entity'] = $entity;
  }

}
