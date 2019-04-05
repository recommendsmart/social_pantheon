<?php

namespace Drupal\drm_core_farm;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drm_core_farm\Entity\RecordType;

/**
 * Access control handler for DRM Core Record entities.
 */
class RecordAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer drm_core_record entities',
          'view any drm_core_record entity',
          'view any drm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer drm_core_record entities',
          'edit any drm_core_record entity',
          'edit any drm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer drm_core_record entities',
          'delete any drm_core_record entity',
          'delete any drm_core_record entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'revert':
        // @todo: more fine grained will be adjusting dynamic permission
        // generation for reverting bundles of records.
        return AccessResult::allowedIfHasPermissions($account, [
          'administer drm_core_record entities',
          'revert all drm_core_record revisions',
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
      /* @var \Drupal\drm_core_farm\Entity\RecordType $record_type_entity */
      $record_type_entity = RecordType::load($entity_bundle);
      $record_type_is_active = $record_type_entity->status();
    }

    return AccessResult::allowedIf($record_type_is_active)
      ->andIf(AccessResult::allowedIfHasPermissions($account, [
        'administer drm_core_record entities',
        'create drm_core_record entities',
        'create drm_core_record entities of bundle ' . $entity_bundle,
      ], 'OR'));
  }

}
