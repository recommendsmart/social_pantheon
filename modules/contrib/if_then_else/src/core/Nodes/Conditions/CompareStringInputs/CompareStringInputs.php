<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\CompareStringInputs;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Condition class defined to compare 2 string inputs and return boolean.
 */
class CompareStringInputs extends Condition {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'compare_string_inputs';
  }

  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => t('Compare Two String Inputs'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\CompareStringInputs\\CompareStringInputs',
      'library' => 'if_then_else/CompareStringInputs',
      'control_class_name' => 'CompareStringInputsControl',
      'compare_options' => [
        [ 'code' => 'equal', 'name' => 'Equal'],
        [ 'code' => 'notequal', 'name' => 'Not Equal'],
        [ 'code' => 'contains', 'name' => 'Input 2 Contains Input 1'],
        [ 'code' => 'startswith', 'name' => 'Input 2 Starts With Input 1'],
        [ 'code' => 'endswith', 'name' => 'Input 2 Ends With Input 1'],
      ],
      'inputs' => [
        'input1' => [
          'label' => t('Input 1'),
          'description' => t('Input 1'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'input2' => [
          'label' => t('Input 2'),
          'description' => t('Input 2'),
          'sockets' => ['string'],
          'required' => TRUE,
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
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!isset($data->compare_type[0]->code) || empty($data->compare_type[0]->code)) {
      $event->errors[] = t('Select a compare type for "@node_name".', ['@node_name' => $event->node->name]);
    }  
  }

  /**
   * Process inputs and set output.
   */
  public function process() {
    $input1 = $this->inputs['input1'];
    $input2 = $this->inputs['input2'];
    $condition_type = $this->data->compare_type[0]->code;
    $case_sensitive = false;
    if(isset($this->data->selection)){
      $case_sensitive = $this->data->selection;
    }
    $output = false;

    switch ($condition_type) {
      case 'equal':
        if($case_sensitive){
          if($input1 == $input2){
            $output = TRUE;
          }
        }else{
          if(strcasecmp($input1,$input2) === 0){
            $output = TRUE;
          }
        }
        break;

      case 'notequal':
        if($case_sensitive){
          if($input1 != $input2){
            $output = TRUE;
          }
        }else{
          if(strcasecmp($input1,$input2) !== 0){
            $output = TRUE;
          }
        }
        break;

      case 'contains':
        if($case_sensitive){
          if(strpos( $input2, $input1 ) !== false){
            $output = TRUE;
          }
        }else{
          if(stripos( $input2, $input1 ) !== false){
            $output = TRUE;
          }
        }
        
        break;

      case 'startswith':
        if($case_sensitive){
          if(strpos($input2, $input1) === 0){
            $output = TRUE;
          }
        }else{
          if(stripos($input2, $input1) === 0){
            $output = TRUE;
          }
        }
        break;

      case 'endswith':
        if($case_sensitive){
          if(substr_compare($input2, $input1, -strlen($input1)) === 0){
            $output = TRUE;
          }
        }else{
          if(substr_compare($input2, $input1, -strlen($input1),strlen($input1),true) === 0){
            $output = TRUE;
          }
        }
        
        break;
    }

    $this->outputs['success'] = $output;
  }

}
