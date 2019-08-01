<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetFieldValue;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use stdClass;

/**
 * Class defined to get value of entity field.
 */
class GetFieldValue extends Action {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'get_form_field_value_action';
  }

  /**
   * Event subscriber for register get field value.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $form_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);
    $field_entity = $if_then_else_utilities->getEntityByFieldName($form_fields);
    $fields_type = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info, 'field_type');

    $event->nodes[static::getName()] = [
      'label' => t('Get Form Field Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetFieldValue\\GetFieldValue',
      'library' => 'if_then_else/GetFieldValue',
      'control_class_name' => 'GetFieldValueControl',
      'form_fields' => $form_fields,
      'form_fields_type' => $fields_type,
      'field_entity_bundle' => $field_entity,
      'component_class_name' => 'GetFieldValueActionComponent',
      'inputs' => [
        'form_state' => [
          'label' => t('Form State'),
          'description' => t('Form state object.'),
          'sockets' => ['form_state'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'field_value' => [
          'label' => t('Field Value'),
          'description' => t('Field Value'),
          'socket' => 'field',
        ],
      ],
    ];
  }

  /**
   * Validate Graph.
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;

    foreach ($nodes as $node) {
      if ($node->data->type == 'event' && $node->data->name != 'form_validate_event') {
        $event->errors[] = t('Get Value of field will only work with Form validate Event');
      }
    }
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
    $this->getValueOfField();
  }

  /**
   * Get field value.
   */
  private function getValueOfField() {
    $form_state = $this->inputs['form_state'];
    $form_fields = $this->data->form_fields;
    $entity = $this->data->selected_entity->code;

    if ($form_fields->code == 'title') {
      $field_value = $form_state->getValue($form_fields->code);
      $output = $field_value[0]['value'];
    }
    else {
      $field_value = $form_state->getValue($form_fields->code);
      $field_type = $this->data->field_type;

      switch ($field_type) {
        case 'list_string':
        case 'string':
        case 'list_integer':
        case 'email':
        case 'datetime':
        case 'list_float':
        case 'list_integer':
        case 'decimal':
        case 'float':
        case 'integer':
        case 'string_long':
          $output = $field_value[0]['value'];
          break;

        case 'boolean':
          $output = $field_value['value'];
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
          $output->fids = $field_value[0]['fids'];
          $output->width = $field_value[0]['width'];
          $output->height = $field_value[0]['height'];
          $output->description = $field_value[0]['description'];
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
