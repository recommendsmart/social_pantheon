<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\ListContainsItemCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\Entity\EntityInterface;

/**
 * List contains item condition class.
 */
class ListContainsItemCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'list_contains_item_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('List Contains Item'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\ListContainsItemCondition\\ListContainsItemCondition',
      'inputs' => [
        'list' => [
          'label' => t('List'),
          'description' => t('The list to be checked.'),
          'sockets' => ['array'],
          'required' => TRUE,
        ],
        'item' => [
          'label' => t('Item'),
          'description' => t('The item to check for.'),
          'sockets' => ['string', 'number', 'object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('Does the list have item?'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $list = $this->inputs['list'];
    $item = $this->inputs['item'];
    $output = FALSE;

    if ($item instanceof EntityInterface && $id = $item->id()) {
      // Check for equal items using the identifier if there is one.
      foreach ($list as $list_item) {
        if ($list_item instanceof EntityInterface && $list_item->id() == $id) {
          $output = TRUE;
        }
      }
    }
    else {
      $output = in_array($item, $list);
    }

    $this->outputs['success'] = $output;

  }

}
