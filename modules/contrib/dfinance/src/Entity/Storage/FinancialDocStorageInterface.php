<?php

namespace Drupal\dfinance\Entity\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface FinancialDocStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Financial Document revision IDs for a specific Financial Document.
   *
   * @param \Drupal\dfinance\Entity\FinancialDocInterface $entity
   *   The Financial Document entity.
   *
   * @return int[]
   *   Financial Document revision IDs (in ascending order).
   */
  public function revisionIds(FinancialDocInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Financial Document author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Financial Document revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\dfinance\Entity\FinancialDocInterface $entity
   *   The Financial Document entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(FinancialDocInterface $entity);

  /**
   * Unsets the language for all Financial Document with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
