<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SaveEntityAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Delete entity action node class.
 */
class SaveEntityAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'save_entity_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Save Entity'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SaveEntityAction\\SaveEntityAction',
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
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
   * {@inheritDoc}.
   */
  public function process() {
    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];

    if (!$entity instanceof EntityInterface) {
      \Drupal::logger('if_then_else')->notice(t("Rule @node_name did not run as the instance of the entity could not be found", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }
    $entity->save();

    // adding the entity object in the output
    $this->outputs['entity'] = $entity;

  }

}