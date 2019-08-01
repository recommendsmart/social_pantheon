<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UrlAliasExistsCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * URL Alias Exists condition class.
 */
class UrlAliasExistsCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'url_alias_exists_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('URL Alias Exists'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UrlAliasExistsCondition\\UrlAliasExistsCondition',
      'inputs' => [
        'alias' => [
          'label' => t('Alias'),
          'description' => t('The alias to see if exists.'),
          'sockets' => ['string.url', 'string'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the system path does not match the given alias (ie: the alias exists).'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $alias = trim($this->inputs['alias']);
    $path = \Drupal::service('path.alias_manager')->getPathByAlias($alias);

    $output = FALSE;
    if ($path != $alias) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
