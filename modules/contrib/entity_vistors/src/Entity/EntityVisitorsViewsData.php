<?php

namespace Drupal\entity_visitors\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Entity visitors entities.
 */
class EntityVisitorsViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
