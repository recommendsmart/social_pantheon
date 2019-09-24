<?php

namespace Drupal\dfinance\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Account Code entities.
 *
 * @ingroup dfinance
 */
class AccountCodeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Account code');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\dfinance\Entity\AccountCode $entity */
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.financial_account_code.edit_form',
      ['financial_account_code' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
