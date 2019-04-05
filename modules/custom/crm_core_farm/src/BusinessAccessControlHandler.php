<?php

namespace Drupal\crm_core_farm;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\crm_core_farm\Entity\BusinessType;

/**
 * Access control handler for CRM Core Business entities.
 */
class BusinessAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_business entities',
          'view any crm_core_business entity',
          'view any crm_core_business entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_business entities',
          'edit any crm_core_business entity',
          'edit any crm_core_business entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_business entities',
          'delete any crm_core_business entity',
          'delete any crm_core_business entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'revert':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer crm_core_business entities',
          'revert business record',
        ], 'OR');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $business_type_is_active = empty($entity_bundle);

    // Load the business type entity.
    if (!empty($entity_bundle)) {
      /* @var \Drupal\crm_core_farm\Entity\FarmType $farm_type_entity */
      $business_type_entity = BusinessType::load($entity_bundle);
      $business_type_is_active = $business_type_entity->status();
    }

    return AccessResult::allowedIf($business_type_is_active)
      ->andIf(AccessResult::allowedIfHasPermissions($account, [
        'administer crm_core_business entities',
        'create crm_core_business entities',
        'create crm_core_business entities of bundle ' . $entity_bundle,
      ], 'OR'));
  }

}
