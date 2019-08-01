<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ClearCacheOfEntityAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Clear Cache Of Entity Action class.
 */
class ClearCacheOfEntityAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'clear_cache_of_entity_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Clear Entity Cache'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ClearCacheOfEntityAction\\ClearCacheOfEntityAction',
      'inputs' => [
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
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
    \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId())->resetCache([$entity->id()]);
  }

}
