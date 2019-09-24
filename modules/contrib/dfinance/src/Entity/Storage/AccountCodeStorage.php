<?php

namespace Drupal\dfinance\Entity\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\dfinance\Entity\AccountCodeInterface;

/**
 * Defines the storage handler class for Account Code entities.
 *
 * This extends the base storage class, adding required special handling for
 * Account Code entities.
 *
 * @ingroup dfinance
 */
class AccountCodeStorage extends SqlContentEntityStorage implements AccountCodeStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AccountCodeInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {financial_account_code_revision} WHERE code=:code ORDER BY vid',
      [':code' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {financial_account_code_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

}
