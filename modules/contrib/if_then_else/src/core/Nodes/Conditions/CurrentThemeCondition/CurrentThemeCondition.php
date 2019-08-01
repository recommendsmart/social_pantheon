<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\CurrentThemeCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * Current Theme condition class.
 */
class CurrentThemeCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'current_theme_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $theme_lists = \Drupal::service('theme_handler')->listInfo();
    $admin_theme_options = [];
    foreach ($theme_lists as $theme) {
      if (!empty($theme->status)) {
        $admin_theme_options[] = ['code' => $theme->getName(), 'name' => $theme->info['name']];
      }
    }
    $event->nodes[static::getName()] = [
      'label' => t('Current Theme'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\CurrentThemeCondition\\CurrentThemeCondition',
      'library' => 'if_then_else/CurrentThemeCondition',
      'control_class_name' => 'CurrentThemeConditionControl',
      'compare_options' => $admin_theme_options,
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the current theme is of the provided theme.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!isset($data->selected_theme[0]->code) || empty($data->selected_theme[0]->code)) {
      $event->errors[] = t('Select a theme for "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $selected_theme = $this->data->selected_theme[0]->code;
    $current_theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();

    $output = FALSE;

    if ($selected_theme == $current_theme) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
