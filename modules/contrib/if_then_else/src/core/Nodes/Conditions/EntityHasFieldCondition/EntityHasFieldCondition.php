<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\EntityHasFieldCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Component\Utility\Html;
/**
 * Entity has field condition class.
 */
class EntityHasFieldCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'entity_has_field_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $entity_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);

    $event->nodes[static::getName()] = [
      'label' => t('Entity Has Field'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\EntityHasFieldCondition\\EntityHasFieldCondition',
      'library' => 'if_then_else/EntityHasFieldCondition',
      'control_class_name' => 'EntityHasFieldConditionControl',
      'entity_fields' => $entity_fields,
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('The entity to check for the provided field.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the provided entity has the provided field.'),
          'socket' => 'bool',
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
        foreach ($node->outputs->text->connections as $connection) {
          if ($connection->input == 'field' &&  empty($node->data->value)) {
            $event->errors[] = t('Enter value for field in "@node_name".', ['@node_name' => $node->name]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if ($data->field_selection == 'list' &&  empty($data->entity_fields)) {
      $event->errors[] = t('Select field that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
    }
    if ($data->field_selection == 'input' &&  (!property_exists($data, 'valueText') || empty($data->valueText))) {
      $event->errors[] = t('Enter field name that you want to check for in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];

    if (!$entity) {
      \Drupal::logger('if_then_else')->notice(t("Rule @node_name did not run as the instance of the entity could not be found", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }

    $field_selection_type = $this->data->field_selection;
    if ($field_selection_type == 'list') {
      $field = $this->data->entity_fields[0]->code;
    }
    elseif ($field_selection_type == 'input') {
      $field = Html::escape(trim($this->data->valueText));
    }

    $this->outputs['success'] = $entity->hasField($field);
  }

}
