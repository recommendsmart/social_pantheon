<?php

namespace Drupal\nbox\Entity\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Nbox entity.
 *
 * @see \Drupal\nbox\Entity\Nbox.
 */
class NboxAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkCreateAccess(AccountInterface $account = NULL, array $context = [], $entity_bundle = NULL) {
    if ($account->hasPermission('administer nbox folder')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return AccessResult::allowedIfHasPermission($account, 'use nbox')->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer nbox entities')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'use nbox')
          ->andIf(AccessResult::allowedIf(in_array($account->id(), $entity->getParticipants())))->addCacheableDependency($entity);

      case 'draft':
        return AccessResult::allowedIfHasPermission($account, 'use nbox')
          ->andIf(AccessResult::allowedIf(!$entity->isPublished()))
          ->andIf(AccessResult::allowedIf($account->id() == $entity->getOwnerId()))
          ->addCacheableDependency($entity);
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

}
