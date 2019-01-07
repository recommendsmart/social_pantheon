<?php

namespace Drupal\niobi_whitelist;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Whitelist Item entities.
 *
 * @ingroup niobi_whitelist
 */
class NiobiWhitelistItemListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Whitelist Item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\niobi_whitelist\Entity\NiobiWhitelistItem */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.niobi_whitelist_item.edit_form',
      ['niobi_whitelist_item' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
