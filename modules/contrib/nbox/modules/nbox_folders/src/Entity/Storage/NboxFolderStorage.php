<?php

namespace Drupal\nbox_folders\Entity\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for Nbox folder entities.
 */
class NboxFolderStorage extends SqlContentEntityStorage implements NboxFolderStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadByUser(int $uid): array {
    return $this->loadByProperties([
      'uid' => $uid,
    ]);
  }

}
