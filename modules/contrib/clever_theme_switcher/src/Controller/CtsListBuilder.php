<?php

namespace Drupal\clever_theme_switcher\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Cts.
 */
class CtsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['id'] = $this->t('Machine name');
    $header['theme'] = $this->t('Theme');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->getLabel();
    $row['id'] = $entity->getId();
    $row['theme'] = $entity->getTheme();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->hasLinkTemplate('manage-conditions')) {
      $operations['manage_conditions'] = [
        'title' => $this->t('Manage conditions'),
        'weight' => 1,
        'url' => $this->ensureDestination($entity->toUrl('manage-conditions')),
      ];
    }
    return $operations;
  }

}
