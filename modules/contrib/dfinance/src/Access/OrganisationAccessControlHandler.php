<?php

namespace Drupal\dfinance\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Organisation entity.
 *
 * @see \Drupal\dfinance\Entity\Organisation.
 */
class OrganisationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    # todo: https://www.drupal.org/project/dfinance/issues/3007054

    /** @var \Drupal\dfinance\Entity\OrganisationInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view finance organisation entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit finance organisation entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete finance organisation entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add finance organisation entities');
  }

}
