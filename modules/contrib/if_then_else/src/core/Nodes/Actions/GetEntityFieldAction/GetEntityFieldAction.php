<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetEntityFieldAction;

use Drupal\Component\Utility\Html;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use stdClass;

/**
 * Class GetEntityFieldAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\GetEntityFieldAction
 */
class GetEntityFieldAction extends Action {

  /**
   * {@inheritDoc}
   */
  public static function getName() {
    return 'get_entity_field_action';
  }

  /**
   * {@inheritDoc}.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $form_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);
    $field_entity = $if_then_else_utilities->getEntityByFieldName($form_fields);
    $fields_type = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info, 'field_type');

    $event->nodes[static::getName()] = [
      'label' => t('Get Entity Field Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetEntityFieldAction\\GetEntityFieldAction',
      'library' => 'if_then_else/GetEntityFieldAction',
      'control_class_name' => 'GetEntityFieldActionControl',
      'component_class_name' => 'GetEntityFieldActionComponent',
      'form_fields' => $form_fields,
      'form_fields_type' => $fields_type,
      'field_entity_bundle' => $field_entity,
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ]
      ],
      'outputs' => [
        'field' => [
          'label' => t('Field Value'),
          'description' => t('Value of the field set in the entity.'),
          'socket' => 'field'
        ]
      ]
    ];
  }

  /**
   * Entity field value validation.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!property_exists($data, 'form_fields')) {
      $event->errors[] = t('Select a field name or enter field name in "@node_name".', ['@node_name' => $event->node->name]);    
    }
   
    if(!property_exists($data, 'selected_entity')){
        $event->errors[] = t('Select an Entity in "@node_name".', ['@node_name' => $event->node->name]);
    }

    if(!property_exists($data, 'selected_bundle')){
      $event->errors[] = t('Select a Bundle in "@node_name".', ['@node_name' => $event->node->name]);
  }
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {
    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];
    $form_fields = $this->data->form_fields;

    if (!$entity) {
      $this->setSuccess(FALSE);
      return;
    }

    if ($form_fields->code == 'title') {
      $output = $entity->getTitle();
    }
    else {
      $field_value = $entity->get($form_fields->code)->getValue();
      $field_type = $this->data->field_type;

      switch ($field_type) {
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
          $output = $field_value[0]['value'];
          break;

        case 'datetime':
          $date_original= new \Drupal\Core\Datetime\DrupalDateTime($field_value[0]['value'], 'UTC');     
          $output = \Drupal::service('date.formatter')->format( $date_original->getTimestamp(), 'custom', 'Y-m-d H:i:s'  );
          break;
          
        case 'boolean':
          $output = $field_value[0]['value'];
          break;

        case 'text':
        case 'text_long':
          $output = new stdClass();
          $output->value = $field_value[0]['value'];
          $output->format = $field_value[0]['format'];
          break;

        case 'text_with_summary':
          $output = new stdClass();
          $output->summary = $field_value[0]['summary'];
          $output->value = $field_value[0]['value'];
          $output->format = $field_value[0]['format'];
          break;

        case 'entity_reference':
          if (isset($field_value['target_id'][0])) {
            $output = $field_value['target_id'][0]['target_id'];
          }
          else {
            $output = $field_value[0]['target_id'];
          }
          break;

        case 'image':
          $output = new stdClass();
          $output->alt = $field_value[0]['alt'];
          $output->fids = $field_value[0]['target_id'];
          $output->width = $field_value[0]['width'];
          $output->height = $field_value[0]['height'];
          $output->description = "";
          $output->title = $field_value[0]['title'];
          break;

        case 'link':
          $output = new stdClass();
          $output->uri = $field_value[0]['uri'];
          $output->title = $field_value[0]['title'];
          break;
      }
    }

    if (!isset($output) && empty($output)) {
      $this->setSuccess(FALSE);
      return;
    }
    else {
      $this->outputs['field_value'] = $output;
    }
  }
}
