<?php

namespace Drupal\entity_visitors;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Entity visitors entity.
 *
 * @see \Drupal\entity_visitors\Entity\EntityVisitors.
 */
class EntityVisitorsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\entity_visitors\Entity\EntityVisitorsInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished entity visitors entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published entity visitors entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit entity visitors entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete entity visitors entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add entity visitors entities');
  }


}
