<?php

namespace Drupal\if_then_else\core\Nodes\Actions\GetEntityFieldAction;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use stdClass;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Class GetEntityFieldAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\GetEntityFieldAction
 */
class GetEntityFieldAction extends Action {
  use StringTranslationTrait;

  /**
   * The ifthenelse utitlities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * The Date Formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utitlities.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The Date Formatter.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities, DateFormatterInterface $date_formatter) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritDoc}.
   */
  public static function getName() {
    return 'get_entity_field_action';
  }

  /**
   * {@inheritDoc}.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $form_fields = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info);
    $field_entity = $this->ifthenelseUtilities->getEntityByFieldName($form_fields);
    $fields_type = $this->ifthenelseUtilities->getFieldsByEntityBundleId($form_entity_info, 'field_type');

    $event->nodes[static::getName()] = [
      'label' => $this->t('Get Entity Field Value'),
      'description' => $this->t('Get Entity Field Value'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\GetEntityFieldAction\\GetEntityFieldAction',
      'classArg' => ['ifthenelse.utilities', 'date.formatter'],
      'library' => 'if_then_else/GetEntityFieldAction',
      'control_class_name' => 'GetEntityFieldActionControl',
      'component_class_name' => 'GetEntityFieldActionComponent',
      'form_fields' => $form_fields,
      'form_fields_type' => $fields_type,
      'field_entity_bundle' => $field_entity,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'field_value' => [
          'label' => $this->t('Field Value'),
          'description' => $this->t('Value of the field set in the entity.'),
          'socket' => 'object.field',
        ],
      ],
    ];
  }

  /**
   * Entity field value validation.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!property_exists($data, 'form_fields')) {
      $event->errors[] = $this->t('Select a field name or enter field name in "@node_name".', ['@node_name' => $event->node->name]);
    }

    if (!property_exists($data, 'selected_entity')) {
      $event->errors[] = $this->t('Select an Entity in "@node_name".', ['@node_name' => $event->node->name]);
    }

    if (!property_exists($data, 'selected_bundle')) {
      $event->errors[] = $this->t('Select a Bundle in "@node_name".', ['@node_name' => $event->node->name]);
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
        case 'email':
        case 'list_float':
        case 'list_integer':
        case 'decimal':
        case 'float':
        case 'integer':
        case 'string_long':
        case 'boolean':
          if (isset($field_value[0]['value'])) {
            if (count($field_value) == 1) {
              $output = $field_value[0]['value'];
            }
            elseif (count($field_value) > 1) {
              for ($i = 0; $i < count($field_value); $i++) {
                if (isset($field_value[$i]['value'])) {
                  $output[] = $field_value[$i]['value'];
                }
              }
            }
          }
          else {
            $output = '';
          }
          break;

        case 'datetime':
          $date_original = new DrupalDateTime($field_value[0]['value'], 'UTC');
          $output = $this->dateFormatter->format($date_original->getTimestamp(), 'custom', 'Y-m-d H:i:s');
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
