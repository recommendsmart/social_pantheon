<?php

namespace Drupal\crm_core_data;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for CRM Core Record type entities.
 */
class RecordTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\crm_core_data\Entity\RecordType $entity */

    // First check permission.
    if (parent::checkAccess($entity, $operation, $account)->isForbidden()) {
      return AccessResult::forbidden();
    }

    switch ($operation) {
      case 'delete':
        // If business instance of this business type exist, you can't
        // delete it.
        $results = \Drupal::entityQuery('crm_core_business')
          ->condition('type', $entity->id())
          ->execute();
        return AccessResult::allowedIf(empty($results));

      case 'view':
      case 'edit':
      case 'update':
        // If the business type is locked, you can't edit it.
        return AccessResult::allowed();
    }
  }

}
