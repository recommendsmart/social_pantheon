<?php

namespace Drupal\nbox\Entity\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the NboxMetadata entity.
 *
 * @see \Drupal\nbox\Entity\NboxMetadata.
 */
class NboxMetadataAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer nbox')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'star':
        return AccessResult::allowedIfHasPermission($account, 'star threads');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'use nbox');

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
