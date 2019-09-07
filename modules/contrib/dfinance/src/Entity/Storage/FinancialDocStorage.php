<?php

namespace Drupal\dfinance\Entity\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dfinance\Entity\FinancialDocInterface;

/**
 * Defines the storage handler class for Financial Document entities.
 *
 * This extends the base storage class, adding required special handling for
 * Financial Document entities.
 *
 * @ingroup dfinance
 */
class FinancialDocStorage extends SqlContentEntityStorage implements FinancialDocStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(FinancialDocInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {financial_doc_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {financial_doc_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(FinancialDocInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {financial_doc_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('financial_doc_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
