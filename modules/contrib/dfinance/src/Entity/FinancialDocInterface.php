<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Financial Document entities.
 *
 * @ingroup dfinance
 */
interface FinancialDocInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Financial Document name.
   *
   * @return string
   *   Name of the Financial Document.
   */
  public function getName();

  /**
   * Sets the Financial Document name.
   *
   * @param string $name
   *   The Financial Document name.
   *
   * @return \Drupal\dfinance\Entity\FinancialDocInterface
   *   The called Financial Document entity.
   */
  public function setName($name);

  /**
   * Gets the Finance Organisation this Financial Document belongs to.
   *
   * @return \Drupal\dfinance\Entity\OrganisationInterface|null
   *   The Finance Organisation which this Financial Doc relates to.
   */
  public function getOrganisation();

  /**
   * Gets the Financial Document creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Financial Document.
   */
  public function getCreatedTime();

  /**
   * Sets the Financial Document creation timestamp.
   *
   * @param int $timestamp
   *   The Financial Document creation timestamp.
   *
   * @return \Drupal\dfinance\Entity\FinancialDocInterface
   *   The called Financial Document entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Financial Document revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Financial Document revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dfinance\Entity\FinancialDocInterface
   *   The called Financial Document entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Financial Document revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Financial Document revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dfinance\Entity\FinancialDocInterface
   *   The called Financial Document entity.
   */
  public function setRevisionUserId($uid);

}
