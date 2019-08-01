<?php

namespace Drupal\if_then_else\core\Nodes\Actions\AddNumbersAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Class defined to execute add numbers action node.
 */
class AddNumbersAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'add_numbers_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => t('Add Numbers'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\AddNumbersAction\\AddNumbersAction',
      'inputs' => [
        'input1' => [
          'label' => t('Input 1'),
          'description' => t('Input 1'),
          'sockets' => ['number'],
        ],
        'input2' => [
          'label' => t('Input 2'),
          'description' => t('Input 2'),
          'sockets' => ['number'],
        ],
        'input3' => [
          'label' => t('Input 3'),
          'description' => t('Input 3'),
          'sockets' => ['number'],
        ],
        'input4' => [
          'label' => t('Input 4'),
          'description' => t('Input 4'),
          'sockets' => ['number'],
        ],
        'input5' => [
          'label' => t('Input 5'),
          'description' => t('Input 5'),
          'sockets' => ['number'],
        ],
      ],
      'outputs' => [
        'output' => [
          'label' => t('Output'),
          'description' => t('Total of input numbers'),
          'socket' => 'number',
        ],
      ],
    ];
  }

  /**
   * Process add number action node.
   */
  public function process() {
    $input_numbers = 0;
    $input_all_inputs = $this->inputs;

    // Get all the inputs and check condition for all the inputs.
    if ($input_all_inputs) {
      // Remove execute vale from inputs.
      unset($input_all_inputs['execute']);
      $input_numbers = array_sum($input_all_inputs);
    }

    $this->outputs['output'] = $input_numbers;
  }

}
