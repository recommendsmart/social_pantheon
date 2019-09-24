<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Account Code entities.
 *
 * @ingroup dfinance
 */
interface AccountCodeInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the label for this Account Code.
   *
   * The label will be the Account Code ID followed by the Account Code Name, for example
   * "101 Income", if for some reason the Account Code Name is null only the Account Code ID
   * will be returned.
   *
   * @return string
   *   Account Code label.
   */
  public function label();

  /**
   * Gets the Account Code name.
   *
   * @return string
   *   Name of the Account Code.
   */
  public function getName();

  /**
   * Sets the Account Code name.
   *
   * @param string $name
   *   The Account Code name.
   *
   * @return \Drupal\dfinance\Entity\AccountCodeInterface
   *   The called Account Code entity.
   */
  public function setName($name);

  /**
   * Gets the Account Code creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Account Code.
   */
  public function getCreatedTime();

  /**
   * Sets the Account Code creation timestamp.
   *
   * @param int $timestamp
   *   The Account Code creation timestamp.
   *
   * @return \Drupal\dfinance\Entity\AccountCodeInterface
   *   The called Account Code entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Account Code revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Account Code revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dfinance\Entity\AccountCodeInterface
   *   The called Account Code entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Account Code revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Account Code revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dfinance\Entity\AccountCodeInterface
   *   The called Account Code entity.
   */
  public function setRevisionUserId($uid);

}
