<?php

namespace Drupal\agerp_core_basic;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\agerp_core_basic\Entity\PartyType;

/**
 * Access control handler for AGERP Core Party entities.
 */
class PartyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer agerp_core_party entities',
          'view any agerp_core_party entity',
          'view any agerp_core_party entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer agerp_core_party entities',
          'edit any agerp_core_party entity',
          'edit any agerp_core_party entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          'administer agerp_core_party entities',
          'delete any agerp_core_party entity',
          'delete any agerp_core_party entity of bundle ' . $entity->bundle(),
        ], 'OR');

      case 'revert':
        // @todo: more fine grained will be adjusting dynamic permission
        // generation for reverting bundles of individuals.
        return AccessResult::allowedIfHasPermissions($account, [
          'administer agerp_core_party entities',
          'revert all agerp_core_party revisions',
        ], 'OR');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $party_type_is_active = empty($entity_bundle);

    // Load the party type entity.
    if (!empty($entity_bundle)) {
      /* @var \Drupal\agerp_core_basic\Entity\PartyType $party_type_entity */
      $party_type_entity = PartyType::load($entity_bundle);
      $party_type_is_active = $party_type_entity->status();
    }

    return AccessResult::allowedIf($party_type_is_active)
      ->andIf(AccessResult::allowedIfHasPermissions($account, [
        'administer agerp_core_party entities',
        'create agerp_core_party entities',
        'create agerp_core_party entities of bundle ' . $entity_bundle,
      ], 'OR'));
  }

}
