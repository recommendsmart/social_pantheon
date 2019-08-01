<?php

namespace Drupal\if_then_else\core\Nodes\Actions\DeleteUrlAliasAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Delete URL alias action class.
 */
class DeleteUrlAliasAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'delete_url_alias_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Delete URL Alias'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\DeleteUrlAliasAction\\DeleteUrlAliasAction',
      'inputs' => [
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
  public function process() {
    // To check path module is enable or not.
    if (!\Drupal::moduleHandler()->moduleExists('path')) {
      \Drupal::logger('if_then_else')->notice(t("Path module is not enabled. Rule @node_name won't execute.", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }

    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];
    if (!$entity instanceof EntityInterface) {
      $this->setSuccess(FALSE);
      return;
    }
    $alias_exists = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $entity->toUrl()->getInternalPath());
    if (empty($alias_exists)) {
      $this->setSuccess(FALSE);
      return;
    }
    // Delete all aliases associated with this entity in the current language.
    $conditions = [
      'source' => '/' . $entity->toUrl()->getInternalPath(),
      'langcode' => $entity->language()->getId(),
    ];
    \Drupal::service('path.alias_storage')->delete($conditions);
  }

}
