<?php

namespace Drupal\if_then_else\core\Nodes\Actions\CreateNewEntityAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Create New Entity action class.
 */
class CreateNewEntityAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'create_new_entity_action';
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
      'label' => t('Create New Entity'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\CreateNewEntityAction\\CreateNewEntityAction',
      'library' => 'if_then_else/CreateNewEntityAction',
      'control_class_name' => 'CreateNewEntityActionControl',
      'entity_info' => $form_entity_info,
      'outputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity Object'),
          'socket' => 'object.entity',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (!property_exists($data, 'selected_entity') || !property_exists($data, 'selected_bundle')) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = t('Select both entity type and bundle in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $type = $this->data->selected_entity->value;
    $bundle = $this->data->selected_bundle->value;
    if (empty($type) || empty($bundle)) {
      $this->setSuccess(FALSE);
      return;
    }
    $entity = \Drupal::entityTypeManager()->getStorage($type)->create(['type' => $bundle]);

    $this->outputs['entity'] = $entity;

  }

}
