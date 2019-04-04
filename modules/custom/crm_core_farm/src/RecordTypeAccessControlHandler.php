<?php

namespace Drupal\crm_core_farm;

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
    /** @var \Drupal\crm_core_farm\Entity\RecordType $entity */

    // First check permission.
    if (parent::checkAccess($entity, $operation, $account)->isForbidden()) {
      return AccessResult::forbidden();
    }

    switch ($operation) {
      case 'delete':
        // If record instance of this record type exist, you can't
        // delete it.
        $results = \Drupal::entityQuery('crm_core_record')
          ->condition('type', $entity->id())
          ->execute();
        return AccessResult::allowedIf(empty($results));

      // @todo Which is it?
      case 'view':
      case 'edit':
      case 'update':
        // If the record type is locked, you can't edit it.
        return AccessResult::allowed();
    }
  }

}
