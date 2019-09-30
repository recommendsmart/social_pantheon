<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetFieldValue;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use stdClass;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * Class defined to get value of entity field.
 */
class GetFieldValue extends Action {
  use StringTranslationTrait;
  /**
   * The ifthenelse utitlities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utitlities.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
  }

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
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $form_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);
    $field_entity = $this->ifthenelseUtilities->getEntityByFieldName($form_fields);
    $fields_type = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info, 'field_type');

    $event->nodes[static::getName()] = [
      'label' => $this->t('Get Form Field Value'),
      'description' => $this->t('Get Form Field Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetFieldValue\\GetFieldValue',
      'classArg' => ['ifthenelse.utilities'],
      'library' => 'if_then_else/GetFieldValue',
      'control_class_name' => 'GetFieldValueControl',
      'form_fields' => $form_fields,
      'form_fields_type' => $fields_type,
      'field_entity_bundle' => $field_entity,
      'component_class_name' => 'GetFieldValueActionComponent',
      'inputs' => [
        'form_state' => [
          'label' => $this->t('Form State'),
          'description' => $this->t('Form state object.'),
          'sockets' => ['form_state'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'field_value' => [
          'label' => $this->t('Field Value'),
          'description' => $this->t('Field Value'),
          'socket' => 'object.field',
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
        $event->errors[] = $this->t('Get Value of field will only work with Form validate Event');
      }
    }
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->form_fields)) {
      $event->errors[] = $this->t('Select a field name to fetch it\' value in "@node_name".', ['@node_name' => $event->node->name]);
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
          if (isset($field_value[0]['value'])) {
            if (count($field_value) == 1) {
              $output = $field_value[0]['value'];
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['value']) && !empty($field_value[$i]['value'])) {
                  $output[] = $field_value[$i]['value'];
                }
              }
            }
          }
          else {
            $output = '';
          }
          break;

        case 'boolean':
          if (isset($field_value['value'])) {
            $output = $field_value['value'];
          }
          else {
            $output = "";
          }
          break;

        case 'text':
        case 'text_long':
          if (isset($field_value[0]['value'])) {
            $output_value = new stdClass();
            if (count($field_value) == 1) {
              $output_value->value = $field_value[0]['value'];
              $output_value->format = $field_value[0]['format'];
              $output = $output_value;
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['value'])) {
                  $output_value->value = $field_value[$i]['value'];
                  $output_value->format = $field_value[$i]['format'];
                  $output[] = $output_value;
                }
              }
            }
          }
          else {
            $output = '';
          }
          break;

        case 'text_with_summary':
          if (isset($field_value[0]['value'])) {
            $output_value = new stdClass();
            if (count($field_value) == 1) {
              $output_value->summary = $field_value[0]['summary'];
              $output_value->value = $field_value[0]['value'];
              $output_value->format = $field_value[0]['format'];
              $output = $output_value;
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['value']) || isset($field_value[$i]['summary'])) {
                  $output_value->summary = $field_value[$i]['summary'];
                  $output_value->value = $field_value[$i]['value'];
                  $output_value->format = $field_value[$i]['format'];
                  $output[] = $output_value;
                }
              }
            }
          }
          else {
            $output = '';
          }
          break;

        case 'entity_reference':
          if (isset($field_value['target_id'][0])) {
            if (count($field_value['target_id']) == 1) {
              $output = $field_value['target_id'][0]['target_id'];
            }
            elseif (count($field_value['target_id']) > 1) {
              for ($i = 0; $i < count($field_value['target_id']); $i++) {
                if (isset($field_value['target_id'][$i]['target_id'])) {
                  $output[] = $field_value['target_id'][$i]['target_id'];
                }
              }
            }
          }
          elseif (isset($field_value[0]['target_id'])) {
            if (count($field_value) == 1) {
              $output = $field_value[0]['target_id'];
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['target_id'])) {
                  $output[] = $field_value[$i]['target_id'];
                }
              }
            }
          }
          else {
            $output = "";
          }
          break;

        case 'image':
          if (isset($field_value[0]['target_id'])) {
            $output_value = new stdClass();
            if (count($field_value) == 1) {
              $output_value->alt = $field_value[0]['alt'];
              $output_value->fids = $field_value[0]['target_id'];
              $output_value->width = $field_value[0]['width'];
              $output_value->height = $field_value[0]['height'];
              $output_value->description = "";
              $output_value->title = $field_value[0]['title'];
              $output = $output_value;
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['target_id'])) {
                  $output_value->alt = $field_value[$i]['alt'];
                  $output_value->fids = $field_value[$i]['target_id'];
                  $output_value->width = $field_value[$i]['width'];
                  $output_value->height = $field_value[$i]['height'];
                  $output_value->description = "";
                  $output_value->title = $field_value[$i]['title'];
                  $output[] = $output_value;
                }
              }
            }
          }
          else {
            $output = '';
          }
          break;

        case 'link':
          if (isset($field_value[0]['uri'])) {
            $output_value = new stdClass();
            if (count($field_value) == 1) {
              $output_value->uri = $field_value[0]['uri'];
              $output_value->title = $field_value[0]['title'];
              $output = $output_value;
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['uri'])) {
                  $output_value->uri = $field_value[$i]['uri'];
                  $output_value->title = $field_value[$i]['title'];
                  $output[] = $output_value;
                }
              }
            }
          }
          else {
            $output = '';
          }
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
