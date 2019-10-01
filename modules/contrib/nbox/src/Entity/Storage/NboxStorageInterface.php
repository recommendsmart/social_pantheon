<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for Nbox entity storage classes.
 */
interface NboxStorageInterface extends ContentEntityStorageInterface {

  /**
   * Load Nbox message by thread.
   *
   * @param \Drupal\nbox\Entity\Storage\NboxThread $thread
   *   Thread.
   *
   * @return \Drupal\nbox\Entity\NboxInterface[]|null
   *   Nbox message.
   */
  public function loadByThread(NboxThread $thread): ?array;

}
