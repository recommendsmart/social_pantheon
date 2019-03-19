<?php

/**
 * @file
 * Contains \Drupal\redhen_liability\LiabilityInterface.
 */

namespace Drupal\redhen_liability;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an interface for defining Liability entities.
 *
 * @ingroup redhen_liability
 */
interface LiabilityInterface extends ContentEntityInterface, EntityChangedInterface {
  /**
   * Gets the Liability type.
   *
   * @return string
   *   The Liability type.
   */
  public function getType();

  /**
   * Sets the Liability name.
   *
   * @param string $name
   *   The Liability name.
   *
   * @return \Drupal\redhen_liability\LiabilityInterface
   *   The called Liability entity.
   */
  public function setName($name);

  /**
   * Gets the Liability creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Liability.
   */
  public function getCreatedTime();

  /**
   * Sets the Liability creation timestamp.
   *
   * @param int $timestamp
   *   The Liability creation timestamp.
   *
   * @return \Drupal\redhen_liability\LiabilityInterface
   *   The called Liability entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns a label for the liability.
   */
  public function label();

  /**
   * Returns the Liability active status indicator.
   *
   * @return bool
   *   TRUE if the Liability is active.
   */
  public function isActive();

  /**
   * Sets the active status of a Liability.
   *
   * @param bool $active
   *   TRUE to set this Liability to active, FALSE to set it to inactive.
   *
   * @return \Drupal\redhen_liability\LiabilityInterface
   *   The called Liability entity.
   */
  public function setActive($active);

}
