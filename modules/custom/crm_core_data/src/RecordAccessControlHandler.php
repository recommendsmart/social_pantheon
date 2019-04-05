<?php

namespace Drupal\crm_core_data;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\crm_core_data\Entity\RecordType;

/**
 * Access control handler for CRM Core Record entities.
 */
class RecordAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_record entities',
          'view any crm_core_record entity',
          'view any crm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_record entities',
          'edit any crm_core_record entity',
          'edit any crm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_record entities',
          'delete any crm_core_record entity',
          'delete any crm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'revert':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_record entities',
          'revert record record',
        ], 'OR');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $record_type_is_active = empty($entity_bundle);

    // Load the record type entity.
    if (!empty($entity_bundle)) {
      /* @var \Drupal\crm_core_data\Entity\DataType $data_type_entity */
      $record_type_entity = RecordType::load($entity_bundle);
      $record_type_is_active = $record_type_entity->status();
    }

    return AccessResult::allowedIf($record_type_is_active)
      ->andIf(AccessResult::allowedIfHasPermissions($account, [
        'administer crm_core_record entities',
        'create crm_core_record entities',
        'create crm_core_record entities of bundle ' . $entity_bundle,
      ], 'OR'));
  }

}
