<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Supplier entities.
 *
 * @ingroup dfinance
 */
interface SupplierInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {

  /**
   * Gets the Supplier name.
   *
   * @return string
   *   Name of the Supplier.
   */
  public function getName();

  /**
   * Sets the Supplier name.
   *
   * @param string $name
   *   The Supplier name.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setName($name);

  /**
   * Gets the Supplier trading name.
   *
   * @return string
   *   Trading name of the Supplier.
   */
  public function getTradingName();

  /**
   * Sets the Supplier trading name.
   *
   * @param string $name
   *   The Supplier trading name.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setTradingName($name);

  /**
   * Gets the Supplier creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Supplier.
   */
  public function getCreatedTime();

  /**
   * Sets the Supplier creation timestamp.
   *
   * @param int $timestamp
   *   The Supplier creation timestamp.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Supplier revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Supplier revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Supplier revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Supplier revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setRevisionUserId($uid);

  /**
   * Gets whether this Supplier is available for use or not, aka whether
   * the Supplier is "published" or "unpublished".
   *
   * @return bool
   *   TRUE if this Supplier is available, FALSE if not
   */
  public function isPublished();

  /**
   * Sets this Supplier to be available for use, aka "published".
   *
   * @param bool|null $published
   *   This parameter does nothing, it only exists because it exists in
   *   EntityPublishedInterface but is deprecated in Drupal 8.3.0 and
   *   will be removed before Drupal 9.0.0. Use this method,
   *   without any parameter, to set the entity as published and
   *   setUnpublished() to set the entity as unpublished.
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setPublished($published = NULL);

  /**
   * Sets this Supplier to be unavailable for use, aka "unpublished".
   *
   * @return \Drupal\dfinance\Entity\SupplierInterface
   *   The called Supplier entity.
   */
  public function setUnpublished();

}
