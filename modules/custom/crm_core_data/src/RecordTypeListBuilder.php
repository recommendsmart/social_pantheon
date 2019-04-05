<?php

namespace Drupal\crm_core_data;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class RecordTypeListBuilder.
 *
 * List builder for the record type entity.
 *
 * @package Drupal\crm_core_data
 *
 * @see \Drupal\crm_core_data\Entity\RecordType
 */
class RecordTypeListBuilder extends ConfigEntityListBuilder {

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

    $row['description'] = Xss::filterAdmin($entity->getDescription());

    return $row + parent::buildRow($entity);
  }

}
