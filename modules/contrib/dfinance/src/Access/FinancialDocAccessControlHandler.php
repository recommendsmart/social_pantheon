<?php

namespace Drupal\dfinance\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Financial Document entity.
 *
 * @see \Drupal\dfinance\Entity\FinancialDoc.
 */
class FinancialDocAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dfinance\Entity\FinancialDocInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view financial document entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit financial document entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete financial document entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add financial document entities');
  }

}
