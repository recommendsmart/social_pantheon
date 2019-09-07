<?php

namespace Drupal\dfinance\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Supplier entity.
 *
 * @see \Drupal\dfinance\Entity\Supplier.
 */
class SupplierAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dfinance\Entity\SupplierInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view finance_supplier entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit finance_supplier entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete finance_supplier entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add finance_supplier entities');
  }

}
