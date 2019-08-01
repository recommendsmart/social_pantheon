<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\PathHasUrlAliasCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Path Has URL Alias condition class.
 */
class PathHasUrlAliasCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'path_has_url_alias_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Path Has URL Alias'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\PathHasUrlAliasCondition\\PathHasUrlAliasCondition',
      'inputs' => [
        'path' => [
          'label' => t('Path'),
          'description' => t('The path to check.'),
          'sockets' => ['string.url', 'string'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the path has an alias in the given language.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $path = trim($this->inputs['path']);
    $alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);

    $output = FALSE;
    if ($alias != $path) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
