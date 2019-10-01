<?php

namespace Drupal\nbox_folders\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\nbox\Entity\NboxMetadataInterface;

/**
 * Provides an interface for defining Nbox folder entities.
 *
 * @ingroup nbox_folders
 */
interface NboxFolderInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the Nbox folder name.
   *
   * @return string
   *   Name of the Nbox folder.
   */
  public function getName();

  /**
   * Sets the Nbox folder name.
   *
   * @param string $name
   *   The Nbox folder name.
   *
   * @return \Drupal\nbox_folders\Entity\NboxFolderInterface
   *   The called Nbox folder entity.
   */
  public function setName($name);

  /**
   * Gets the Nbox folder creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Nbox folder.
   */
  public function getCreatedTime();

  /**
   * Move the metadata thread object to the folder.
   *
   * @param \Drupal\nbox\Entity\NboxMetadataInterface $nboxMetadata
   *   Nbox metadata object.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   Nbox metadata object.
   */
  public function moveMetadataToFolder(NboxMetadataInterface $nboxMetadata): NboxMetadataInterface;

}
