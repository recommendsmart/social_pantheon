<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UserHasEntityFieldAccessCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * User has entity field access condition class.
 */
class UserHasEntityFieldAccessCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'user_has_entity_field_access_condition';
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
      'label' => t('User Has Entity Field Access'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UserHasEntityFieldAccessCondition\\UserHasEntityFieldAccessCondition',
      'library' => 'if_then_else/UserHasEntityFieldAccessCondition',
      'control_class_name' => 'UserHasEntityFieldAccessConditionControl',
      'form_fields' => $form_fields,
      'opt_options' => [
        ['code' => 'view', 'name' => 'View'],
        ['code' => 'edit', 'name' => 'Edit'],
      ],
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('The entity to check access on.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
        'user' => [
          'label' => t('User'),
          'description' => t('The user account to check access against.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the user has access to the field on the entity, FALSE otherwise.'),
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
    if (empty($data->form_fields)|| empty($data->opt_form_fields)) {
      $event->errors[] = t('Select both field and operation in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $entity = $this->inputs['entity'];
    $user = $this->inputs['user'];

    $selected_field = $this->data->form_fields[0]->code;
    $selected_operation = $this->data->opt_form_fields[0]->code;
    if (!$entity->hasField($selected_field)) {
      $this->outputs['success'] =  FALSE;
      return;
    }

    $access = \Drupal::entityTypeManager()->getAccessControlHandler($entity->getEntityTypeId());
    if(!$access->access($entity, $selected_operation, $user)){
      $this->outputs['success'] =  FALSE;
      return;
    }
    $definition = $entity->getFieldDefinition($selected_field);
    $items = $entity->get($selected_field);

    if ($access->fieldAccess($selected_operation, $definition, $user, $items)) {
      $this->outputs['success'] =  TRUE;
      return;
    }

  }

}
