<?php

namespace Drupal\if_then_else\core\Nodes\Actions\UnpublishNodeAction;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Unpublish action node class.
 */
class UnpublishNodeAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'unpublish_node_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Unpublish Node'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\UnpublishNodeAction\\UnpublishNodeAction',
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
   * {@inheritDoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;

    foreach ($nodes as $node) {

      if ($node->data->type == 'event' && $node->data->name == 'entity_load_event') {
        $event->errors[] = t('Event trigger is an entity load. This may call the If Then Else flow to go into an infinity loop.');
      }
      if ($node->data->type == 'value' && $node->data->name == 'entity_value') {
        if (!property_exists($node->data, 'selected_entity')) {
          $event->errors[] = t('There are an error to process this rule. Please select content entity to process "@node_name" rule', ['@node_name' => $event->node->name]);
        }
        elseif ($node->data->selected_entity->value != 'node') {
          $event->errors[] = t('There are an error to process this rule. Please select content entity to process "@node_name" rule', ['@node_name' => $event->node->name]);
        }
      }
    }
  }

  /**
   * {@inheritDoc}
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

    $node->setPublished(FALSE);
    $node->save();
  }
}
