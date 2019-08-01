<?php

namespace Drupal\if_then_else\core\Nodes\Actions\MakeFieldsRequiredAction;

use Drupal\Component\Utility\Html;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Class defined to execute make fields required action node.
 */
class MakeFieldsRequiredAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'make_fields_required_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Fetch all fields for config entity bundles.
    $if_then_else_utilities = \Drupal::service('ifthenelse.utilities');
    $form_entity_info = $if_then_else_utilities->getContentEntitiesAndBundles();
    $form_fields = $if_then_else_utilities->getFieldsByEntityBundleId($form_entity_info);

    $event->nodes[static::getName()] = [
      'label' => t('Make Fields Required'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\MakeFieldsRequiredAction\\MakeFieldsRequiredAction',
      'library' => 'if_then_else/MakeFieldsRequiredAction',
      'control_class_name' => 'MakeFieldsRequiredControl',
      'form_fields' => $form_fields,
      'inputs' => [
        'form' => [
          'label' => t('Form'),
          'description' => t('Form object.'),
          'sockets' => ['form'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Validation for make fields required action node.
   */
  public function validateNode(NodeValidationEvent $event) {
    // Make sure that form_fields array is not empty.
    if (!count($event->node->data->form_fields)) {
      $event->errors[] = t('Select at least one field in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process make fields required action node.
   */
  public function process() {
    $this->makeSingleFieldsRequired();
  }

  /**
   * Make selected fields required.
   */
  private function makeSingleFieldsRequired() {
    $field_selection_type = $this->data->field_selection;
    if ($field_selection_type == 'list') {
      $form_fields = $this->data->form_fields;
    }
    elseif ($field_selection_type == 'input') {
      $form_fields = explode(',', Html::escape($this->data->valueText));
    }
    else {
      $form_fields = [];
    }

    $form = &$this->inputs['form'];

    foreach ($form_fields as $field) {
      // Field code is field machine name.
      if ($field_selection_type == 'list') {
        $field_code = $field->code;
      }
      elseif ($field_selection_type == 'input') {
        $field_code = trim($field);
      }

      if (isset($form[$field_code])) {
        if ($form[$field_code]['#type'] == 'container') {
          if (isset($form[$field_code]['widget'])) {
            if (isset($form[$field_code]['widget']['#type'])) {
              if (isset($form[$field_code]['widget']['#type']) == 'select') {
                $form[$field_code]['widget']['#required'] = TRUE;
              }
            }
            else {
              foreach ($form[$field_code]['widget'] as $k => $value) {
                if (strpos($k, '#') !== FALSE) {
                  // Skip all keys which have #.
                  continue;
                }

                $form[$field_code]['widget'][$k]['#required'] = TRUE;
              }
            }
          }
        }
        else {
          $form[$field_code]['#required'] = TRUE;
        }
      }
    }
  }

}
