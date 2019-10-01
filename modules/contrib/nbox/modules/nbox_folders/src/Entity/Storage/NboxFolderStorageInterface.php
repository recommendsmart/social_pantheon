<?php

namespace Drupal\nbox_folders\Entity\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for Nbox folder entity storage classes.
 */
interface NboxFolderStorageInterface extends ContentEntityStorageInterface {

  /**
   * Load the Nbox folders for a user.
   *
   * @param int $uid
   *   User to lookup.
   *
   * @return \Drupal\nbox_folders\Entity\NboxFolder[]
   *   Nbox metadata.
   */
  public function loadByUser(int $uid): array;

}
