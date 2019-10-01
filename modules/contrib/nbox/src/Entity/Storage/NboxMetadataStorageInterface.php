<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\nbox\Entity\NboxInterface;
use Drupal\nbox\Entity\NboxMetadataInterface;
use Drupal\nbox\Entity\NboxThreadInterface;

/**
 * Defines an interface for Nbox metadata entity storage classes.
 */
interface NboxMetadataStorageInterface extends ContentEntityStorageInterface {

  /**
   * Create Nbox metadata from Nbox message.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   * @param int $uid
   *   User ID.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   Nbox metadata.
   */
  public function createFromNbox(NboxInterface $nbox, int $uid): NboxMetadataInterface;

  /**
   * Load the Nbox metadata by the sender of the Nbox message.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface|null
   *   Nbox metadata.
   */
  public function loadBySender(NboxInterface $nbox): ?NboxMetadataInterface;

  /**
   * Load the Nbox metadata by a recipient of the Nbox message.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   * @param \Drupal\Core\Session\AccountInterface $participant
   *   Recipient.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface|null
   *   Nbox metadata.
   */
  public function loadByParticipant(NboxInterface $nbox, AccountInterface $participant): ?NboxMetadataInterface;

  /**
   * Load the Nbox metadata by the participants of the Nbox message.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface[]
   *   List of metadata entities keyed by user id.
   */
  public function loadByParticipants(NboxInterface $nbox): array;

  /**
   * Load the Nbox metadata by the recipients of the Nbox message.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface[]
   *   List of metadata entities keyed by user id.
   */
  public function loadByRecipients(NboxInterface $nbox): array;

  /**
   * Load the Nbox metadata by a participant in the Nbox thread.
   *
   * @param \Drupal\Core\Session\AccountInterface $participant
   *   Participant.
   * @param \Drupal\nbox\Entity\NboxThreadInterface $nboxThread
   *   Thread.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface|null
   *   Nbox metadata.
   */
  public function loadByParticipantInThread(AccountInterface $participant, NboxThreadInterface $nboxThread): ?NboxMetadataInterface;

  /**
   * Load the Nbox metadata by the participants of the Nbox thread.
   *
   * @param \Drupal\nbox\Entity\NboxThreadInterface $nboxThread
   *   Thread.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface[]
   *   List of metadata entities keyed by user id.
   */
  public function loadByParticipantsInThread(NboxThreadInterface $nboxThread): array;

}
