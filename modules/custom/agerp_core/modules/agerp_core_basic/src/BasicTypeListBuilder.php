<?php

namespace Drupal\agerp_core_basic;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class BasicTypeListBuilder.
 *
 * List builder for the basic type entity.
 *
 * @package Drupal\agerp_core_basic
 * @see \Drupal\agerp_core_basic\Entity\BasicType
 */
class BasicTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];

    $header['title'] = $this->t('Name');

    $header['description'] = [
      'data' => $this->t('Description'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];

    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];

    $row['description'] = Xss::filterAdmin($entity->description);

    return $row + parent::buildRow($entity);
  }

}
