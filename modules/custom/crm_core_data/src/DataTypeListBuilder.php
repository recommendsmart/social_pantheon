<?php

/**
 * @file
 * Contains \Drupal\crm_core_data\DataTypeListBuilder.
 */

namespace Drupal\crm_core_data;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class DataTypeListBuilder
 *
 * List builder for the data type entity.
 *
 * @package Drupal\crm_core_data
 * @see \Drupal\crm_core_data\Entity\DataType
 */
class DataTypeListBuilder extends ConfigEntityListBuilder {

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
