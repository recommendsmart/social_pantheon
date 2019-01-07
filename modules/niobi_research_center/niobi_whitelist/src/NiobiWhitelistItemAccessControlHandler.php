<?php

namespace Drupal\niobi_whitelist;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Whitelist Item entity.
 *
 * @see \Drupal\niobi_whitelist\Entity\NiobiWhitelistItem.
 */
class NiobiWhitelistItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\niobi_whitelist\Entity\NiobiWhitelistItemInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view whitelist item entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit whitelist item entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete whitelist item entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add whitelist item entities');
  }

}
