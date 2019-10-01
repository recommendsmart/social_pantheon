<?php

namespace Drupal\nbox_folders\Entity\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Nbox folder entity.
 *
 * @see \Drupal\nbox_folders\Entity\NboxFolder.
 */
class NboxFolderAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = [], $return_as_object = FALSE) {
    if ($account->hasPermission('administer nbox folder')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return AccessResult::allowedIfHasPermission($account, 'use nbox folders')->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer nbox folder')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    if (in_array($operation, ['view', 'update', 'delete'])) {
      return AccessResult::allowedIfHasPermission($account, 'use nbox folders')
        ->andIf(AccessResult::allowedIf($account->id() === $entity->getOwnerId()))->addCacheableDependency($entity);
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral()->cachePerPermissions();
  }

}
