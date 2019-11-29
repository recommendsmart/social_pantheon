<?php

namespace Drupal\agerp_core_basic;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for AGERP Core Party type entities.
 */
class PartyTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\agerp_core_basic\Entity\PartyType $entity */

    // First check permission.
    if (parent::checkAccess($entity, $operation, $account)->isForbidden()) {
      return AccessResult::forbidden();
    }

    switch ($operation) {
      case 'delete':
        // If party instance of this party type exist, you can't
        // delete it.
        $results = \Drupal::entityQuery('agerp_core_party')
          ->condition('type', $entity->id())
          ->execute();
        return AccessResult::allowedIf(empty($results));

      // @todo Which is it?
      case 'view':
      case 'edit':
      case 'update':
        // If the individual type is locked, you can't edit it.
        return AccessResult::allowed();
    }
  }

}
