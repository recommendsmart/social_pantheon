<?php

namespace Drupal\if_then_else\core\Nodes\Actions\DemoteNodeAction;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Demote a node to front action class.
 */
class DemoteNodeAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'demote_node_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Un-promote Node'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\DemoteNodeAction\\DemoteNodeAction',
      'inputs' => [
        'node' => [
          'label' => t('Nid / Node'),
          'description' => t('Nid or Node object. Can be of any bundle.'),
          'sockets' => ['number', 'object.entity.node'],
          'required' => TRUE,
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

      if ($node->data->type == 'event' && $node->data->name == 'entity_load_event') {
        if ((property_exists($node->data, 'selected_entity') && $node->data->selected_entity->value == 'node') ||
          (property_exists($node->data, 'selection') && $node->data->selection == 'all')) {
          $event->errors[] = t('Event trigger is an entity load. This may call the If Then Else flow to go into an infinity loop.');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $node = $this->inputs['node'];

    if (is_numeric($node)) {
      $node = Node::load($node);
      if (empty($node)) {
        $this->setSuccess(FALSE);
        return;
      }
    }
    elseif (!$node instanceof NodeInterface) {
      $this->setSuccess(FALSE);
      return;
    }
    try {
      $node->setPromoted(FALSE);
      $node->save();
    }
    catch (\Exception $e) {
      watchdog_exception('if_then_else', $e);
    }
  }

}
