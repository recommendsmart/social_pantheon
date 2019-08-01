<?php

namespace Drupal\if_then_else\core\Nodes\Actions\NonStickyNodeAction;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;

/**
 * Make a node non sticky action class.
 */
class NonStickyNodeAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'non_sticky_node_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Make Node Non-sticky'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\NonStickyNodeAction\\NonStickyNodeAction',
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
   * {@inheritDoc}.
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
   * {@inheritDoc}.
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

    $node->setSticky(FALSE);
    $node->save();
  }

}
