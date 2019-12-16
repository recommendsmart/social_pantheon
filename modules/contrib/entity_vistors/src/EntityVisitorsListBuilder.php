<?php

namespace Drupal\entity_visitors;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Entity visitors entities.
 *
 * @ingroup entity_visitors
 */
class EntityVisitorsListBuilder extends EntityListBuilder  {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Visited Entity Id');
    $header['name'] = $this->t('Entity Type');
    $header['visitors'] = $this->t('Visitors');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\entity_visitors\Entity\EntityVisitors $entity */
    $row['id'] = $entity->get('field_visited_entity_id')->getString();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.entity_visitors.edit_form',
      ['entity_visitors' => $entity->id()]
    );
    $row['visitors'] = $entity->get('field_visited_entity_visitors')->getString();
    return $row + parent::buildRow($entity);
  }

}
