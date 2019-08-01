<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\EntityIsOfTypeCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Entity is of type condition class.
 */
class EntityIsOfTypeCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'entity_is_of_type_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => t('Entity Type'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\EntityIsOfTypeCondition\\EntityIsOfTypeCondition',
      'library' => 'if_then_else/EntityIsOfTypeCondition',
      'control_class_name' => 'EntityIsOfTypeConditionControl',
      'entity_info' => $form_entity_info,
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('The entity to check for a type.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the entity is of the provided type.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (!property_exists($data, 'selected_entity')) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = t('Select entity type in "@node_name".', ['@node_name' => $event->node->name]);
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
    $type = $this->data->selected_entity->value;

    $entity_type = $entity->getEntityTypeId();

    $output = FALSE;
    // To match the entity's type with the specified values.
    if ($entity_type == $type) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
