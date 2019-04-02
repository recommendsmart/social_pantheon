<?php

namespace Drupal\business\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Business entity entities.
 *
 * @ingroup business
 */
interface BusinessEntityInterface extends EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Business entity name.
   *
   * @return string
   *   Name of the Business entity.
   */
  public function getName();

  /**
   * Sets the Business entity name.
   *
   * @param string $name
   *   The Business entity name.
   *
   * @return \Drupal\business\Entity\BusinessEntityInterface
   *   The called Business entity entity.
   */
  public function setName($name);

  /**
   * Gets the Business entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Business entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Business entity creation timestamp.
   *
   * @param int $timestamp
   *   The Business entity creation timestamp.
   *
   * @return \Drupal\business\Entity\BusinessEntityInterface
   *   The called Business entity entity.
   */
  public function setCreatedTime($timestamp);
}
