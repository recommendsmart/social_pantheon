<?php

namespace Drupal\niobi_whitelist\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Whitelist Item entities.
 */
class NiobiWhitelistItemViewsData extends EntityViewsData {

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
