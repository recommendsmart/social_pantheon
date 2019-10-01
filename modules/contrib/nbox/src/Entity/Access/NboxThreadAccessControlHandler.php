<?php

namespace Drupal\nbox\Entity\Access;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\nbox\Entity\Storage\NboxMetadataStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access controller for the NboxThread entity.
 *
 * @see \Drupal\nbox\Entity\NboxThread.
 */
class NboxThreadAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * Metadata storage.
   *
   * @var \Drupal\nbox\Entity\Storage\NboxMetadataStorage
   */
  protected $metadataStorage;

  /**
   * NboxThreadAccessControlHandler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\nbox\Entity\Storage\NboxMetadataStorage $metadataStorage
   *   Nbox metadata storage.
   */
  public function __construct(EntityTypeInterface $entity_type, NboxMetadataStorage $metadataStorage) {
    parent::__construct($entity_type);
    $this->metadataStorage = $metadataStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage('nbox_metadata')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\nbox\Entity\NboxThread $entity */
    if ($account->hasPermission('administer nbox')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'use nbox')
          ->andIf(AccessResult::allowedIf($this->metadataStorage->loadByParticipantInThread($account, $entity) !== NULL))->addCacheableDependency($entity);

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
