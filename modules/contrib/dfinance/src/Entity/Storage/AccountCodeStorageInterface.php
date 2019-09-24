<?php

namespace Drupal\dfinance\Entity\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface AccountCodeStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Account Code revision IDs for a specific Account Code.
   *
   * @param \Drupal\dfinance\Entity\AccountCodeInterface $entity
   *   The Account Code entity.
   *
   * @return int[]
   *   Account Code revision IDs (in ascending order).
   */
  public function revisionIds(AccountCodeInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Account Code author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Account Code revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

}
