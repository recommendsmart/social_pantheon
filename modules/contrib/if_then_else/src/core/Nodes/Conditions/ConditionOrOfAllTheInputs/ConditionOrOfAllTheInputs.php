<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\ConditionOrOfAllTheInputs;

use Drupal\Core\Form\FormState;
use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Class defined to process make all fields required action.
 */
class ConditionOrOfAllTheInputs extends Condition {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'condition_or_of_all_the_inputs';
  }

  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => t('Boolean OR'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\ConditionOrOfAllTheInputs\\ConditionOrOfAllTheInputs',
      'library' => 'if_then_else/ConditionOrOfAllTheInputs',
      'control_class_name' => 'FormIdConditionOrOfAllTheInputs',
      'inputs' => [
        'input1' => [
          'label' => t('Input 1'),
          'description' => t('Did the condition pass?'),
          'sockets' => ['bool'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => t('Input 2'),
          'description' => t('Did the condition pass?'),
          'sockets' => ['bool'],
          'required' => TRUE,
        ],
        'input3' => [
          'label' => t('Input 3'),
          'description' => t('Did the condition pass?'),
          'sockets' => ['bool']
        ],
        'input4' => [
          'label' => t('Input 4'),
          'description' => t('Did the condition pass?'),
          'sockets' => ['bool']
        ],
        'input5' => [
          'label' => t('Input 5'),
          'description' => t('Did the condition pass?'),
          'sockets' => ['bool']
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('Did the condition pass?'),
          'socket' => 'bool'
        ],
      ]
    ];
  }

  /**
   * Process inputs and set output.
   */
  public function process() {
    $condition_status = FALSE;
    $conditions_all_inputs = $this->inputs;

    // Get all the inputs and check condition for all the inputs
    if ($conditions_all_inputs) {
      if(in_array(FALSE, $conditions_all_inputs, TRUE) === false){
        $condition_status = TRUE;
      }
      else if(in_array(TRUE, $conditions_all_inputs, TRUE) === false){
        $condition_status = FALSE;
      }
      else{
        $condition_status = TRUE;
      }
    }

    $this->outputs['success'] = $condition_status;
  }

}
