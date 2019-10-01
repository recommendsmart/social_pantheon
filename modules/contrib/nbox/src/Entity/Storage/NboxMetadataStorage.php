<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\nbox\Entity\NboxInterface;
use Drupal\nbox\Entity\NboxMetadataInterface;
use Drupal\nbox\Entity\NboxThreadInterface;

/**
 * Defines the storage handler class for Nbox metadata entities.
 *
 * This extends the base storage class, adding required special handling for
 * loading nbox metadata entities based on nbox message and thread information.
 */
class NboxMetadataStorage extends SqlContentEntityStorage implements NboxMetadataStorageInterface {

  /**
   * Ensure that the given Nbox entity has been saved.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function ensureSave(NboxInterface $nbox) {
    // An non-unsaved entity cannot have any properties.
    if ($nbox->id() === NULL) {
      throw new EntityStorageException('Cannot load metadata for an unsaved nbox.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createFromNbox(NboxInterface $nbox, int $uid): NboxMetadataInterface {
    $this->ensureSave($nbox);

    $metadata = $this->create([
      'nbox_thread_id' => $nbox->getThreadId(),
      'uid' => $uid,
      'most_recent' => $nbox->id(),
      'message_count' => 1,
      'attachment' => $nbox->hasAttachment(),
    ]);
    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function loadBySender(NboxInterface $nbox): ?NboxMetadataInterface {
    $this->ensureSave($nbox);

    $nboxMetadataList = $this->loadByProperties([
      'nbox_thread_id' => $nbox->getThreadId(),
      'uid' => $nbox->getOwnerId(),
    ]);
    if (count($nboxMetadataList) > 0) {
      $nboxMetadata = reset($nboxMetadataList);
      return $nboxMetadata;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByParticipant(NboxInterface $nbox, AccountInterface $participant): ?NboxMetadataInterface {
    $this->ensureSave($nbox);

    return $this->loadByParticipantInThread($participant, $nbox->getThread());
  }

  /**
   * {@inheritdoc}
   */
  public function loadByParticipants(NboxInterface $nbox): array {
    $this->ensureSave($nbox);

    $usersMetadata = [];
    $participants = $nbox->getParticipants();
    $results = $this->loadByProperties([
      'nbox_thread_id' => $nbox->getThreadId(),
      'uid' => $participants,
    ]);
    foreach ($results as $result) {
      $usersMetadata[$result->getOwnerId()] = $result;
    }

    return $usersMetadata;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByRecipients(NboxInterface $nbox): array {
    $this->ensureSave($nbox);

    $usersMetadata = [];
    $recipients = array_merge(...array_values($nbox->getRecipientsWithBcc()));
    $results = $this->loadByProperties([
      'nbox_thread_id' => $nbox->getThreadId(),
      'uid' => $recipients,
    ]);
    foreach ($results as $result) {
      $usersMetadata[$result->getOwnerId()] = $result;
    }

    return $usersMetadata;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByParticipantInThread(AccountInterface $participant, NboxThreadInterface $nboxThread): ?NboxMetadataInterface {
    $nboxMetadataList = $this->loadByProperties([
      'nbox_thread_id' => $nboxThread->id(),
      'uid' => $participant->id(),
    ]);
    if (count($nboxMetadataList) > 0) {
      $nboxMetadata = reset($nboxMetadataList);
      return $nboxMetadata;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByParticipantsInThread(NboxThreadInterface $nboxThread): array {
    $usersMetadata = [];
    $results = $this->loadByProperties([
      'nbox_thread_id' => $nboxThread->id(),
    ]);
    foreach ($results as $result) {
      $usersMetadata[$result->getOwnerId()] = $result;
    }

    return $usersMetadata;
  }

}
