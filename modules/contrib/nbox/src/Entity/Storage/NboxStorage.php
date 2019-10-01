<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\nbox\Entity\NboxThread;

/**
 * Defines the storage handler class for Nbox thread entities.
 */
class NboxStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function loadByThread(NboxThread $thread): ?array {
    $result = $this->loadByProperties([
      'nbox_thread_id' => $thread->id(),
    ]);

    if (count($result) > 0) {
      return $result;
    }

    return NULL;
  }

}
