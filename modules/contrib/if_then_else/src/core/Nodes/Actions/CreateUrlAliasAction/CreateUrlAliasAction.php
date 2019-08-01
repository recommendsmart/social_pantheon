<?php

namespace Drupal\if_then_else\core\Nodes\Actions\CreateUrlAliasAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Component\Utility\UrlHelper;

/**
 * Create URL alias action class.
 */
class CreateUrlAliasAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'create_url_alias_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Create URL Alias'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\CreateUrlAliasAction\\CreateUrlAliasAction',
      'inputs' => [
        'alias' => [
          'label' => t('Alias'),
          'description' => t('Set alias to entity.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'entity' => [
          'label' => t('Entity'),
          'description' => t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        if (!property_exists($node->data, 'value') || empty($node->data->value)) {
          $event->errors[] = t('Enter the alias in "@node_name".', ['@node_name' => $node->name]);
        }
        else {
          // To check valid url
          if (!UrlHelper::isValid($node->data->value, $absolute = FALSE)) {
            $event->errors[] = t('@alias is not a valid URL in "@node_name".', ['@alias' => $node->data->value,'@node_name' => $node->name]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    // To check path module is enable or not.
    if (!\Drupal::moduleHandler()->moduleExists('path')) {
      \Drupal::logger('if_then_else')->notice(t("Path module is not enabled. Rule @node_name won't execute.", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }

    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];
    $alias = $this->inputs['alias'];
    // Checking slash if not exist then adding slash to alias
    $alias = rtrim(trim(trim($alias), ''), "\\/");
    if ($alias[0] !== '/') {
      $alias = '/'.$alias;
    }
    if (!$entity instanceof EntityInterface || empty($alias)) {
      $this->setSuccess(FALSE);
      return;
    }
    $alias_langcode = $entity->language()->getId();
    $alias_exists = \Drupal::service('path.alias_storage')->aliasExists($alias, $alias_langcode);
    if (!$alias_exists) {
      \Drupal::service('path.alias_storage')->save('/' . $entity->toUrl()->getInternalPath(), $alias, $alias_langcode);
    }
  }

}
