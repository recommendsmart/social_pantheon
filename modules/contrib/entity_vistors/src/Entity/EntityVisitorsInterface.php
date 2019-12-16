<?php

namespace Drupal\entity_visitors\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Entity visitors entities.
 *
 * @ingroup entity_visitors
 */
interface EntityVisitorsInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Entity visitors name.
   *
   * @return string
   *   Name of the Entity visitors.
   */
  public function getName();

  /**
   * Sets the Entity visitors name.
   *
   * @param string $name
   *   The Entity visitors name.
   *
   * @return \Drupal\entity_visitors\Entity\EntityVisitorsInterface
   *   The called Entity visitors entity.
   */
  public function setName($name);

  /**
   * Gets the Entity visitors creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Entity visitors.
   */
  public function getCreatedTime();

  /**
   * Sets the Entity visitors creation timestamp.
   *
   * @param int $timestamp
   *   The Entity visitors creation timestamp.
   *
   * @return \Drupal\entity_visitors\Entity\EntityVisitorsInterface
   *   The called Entity visitors entity.
   */
  public function setCreatedTime($timestamp);

}
