<?php

namespace Drupal\dfinance\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Organisation entities.
 *
 * @ingroup dfinance
 */
class OrganisationListBuilder extends EntityListBuilder {

  /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
  private $route_match;

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match')
    );
  }

  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RouteMatchInterface $route_match) {
    parent::__construct($entity_type, $storage);
    $this->route_match = $route_match;
  }

  /**
   * @return bool
   */
  private function isAdminView() {
    return $this->route_match->getRouteName() == 'entity.finance_organisation.collection';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    return $this->isAdminView() ? $header + parent::buildHeader() : $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\dfinance\Entity\Organisation */
    $row['name'] = $entity->toLink();
    return $this->isAdminView() ? $row + parent::buildRow($entity) : $row;
  }

}
