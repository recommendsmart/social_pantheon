<?php

/**
 * @file
 * Contains \Drupal\crm_core_farm\FarmTypeListBuilder.
 */

namespace Drupal\crm_core_farm;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class FarmTypeListBuilder
 *
 * List builder for the farm type entity.
 *
 * @package Drupal\crm_core_farm
 * @see \Drupal\crm_core_farm\Entity\FarmType
 */
class FarmTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array();

    $header['title'] = $this->t('Name');

    $header['description'] = array(
      'data' => $this->t('Description'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = array();

    $row['title'] = array(
      'data' => $entity->label(),
      'class' => array('menu-label'),
    );

    $row['description'] = Xss::filterAdmin($entity->description);

    return $row + parent::buildRow($entity);
  }
}
