<?php

namespace Drupal\niobi_whitelist\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Whitelist Item entities.
 *
 * @ingroup niobi_whitelist
 */
interface NiobiWhitelistItemInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Whitelist Item name.
   *
   * @return string
   *   Name of the Whitelist Item.
   */
  public function getName();

  /**
   * Sets the Whitelist Item name.
   *
   * @param string $name
   *   The Whitelist Item name.
   *
   * @return \Drupal\niobi_whitelist\Entity\NiobiWhitelistItemInterface
   *   The called Whitelist Item entity.
   */
  public function setName($name);

  /**
   * Gets the Whitelist Item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Whitelist Item.
   */
  public function getCreatedTime();

  /**
   * Sets the Whitelist Item creation timestamp.
   *
   * @param int $timestamp
   *   The Whitelist Item creation timestamp.
   *
   * @return \Drupal\niobi_whitelist\Entity\NiobiWhitelistItemInterface
   *   The called Whitelist Item entity.
   */
  public function setCreatedTime($timestamp);

}
