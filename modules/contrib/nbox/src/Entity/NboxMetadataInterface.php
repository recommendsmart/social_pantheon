<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining Nbox metadata entities.
 *
 * @ingroup nbox
 */
interface NboxMetadataInterface extends ContentEntityInterface {

  /**
   * Thread has not been deleted by user.
   */
  public const NBOX_DELETE_FALSE = 'not_deleted';

  /**
   * Thread has been moved to trash by user.
   */
  public const NBOX_DELETE_MARKED = 'marked_for_deletion';

  /**
   * Thread has been deleted from trash.
   *
   * The thread will not be deleted from DB until all thread participants have
   * marked the thread NBOX_DELETE_PERMANENT.
   */
  public const NBOX_DELETE_PERMANENT = 'can_be_deleted';

  /**
   * Gets the Nbox thread.
   *
   * @return int
   *   The nbox thread or NULL.
   */
  public function getThread(): NboxThread;

  /**
   * Gets the Nbox thread ID.
   *
   * @return int
   *   The Nbox thread ID or NULL.
   */
  public function getThreadId(): int;

  /**
   * Returns the entity owner's user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The owner user entity.
   */
  public function getOwner(): UserInterface;

  /**
   * Returns the entity owner's user ID.
   *
   * @return int|null
   *   The owner user ID, or NULL in case the user ID field has not been set on
   *   the entity.
   */
  public function getOwnerId(): int;

  /**
   * Gets the Nbox metadata delete status.
   *
   * @return string
   *   The nbox delete status.
   */
  public function getDeleteStatus(): string;

  /**
   * Either move status to trash or if in trash to permanent delete.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function markDelete(): NboxMetadataInterface;

  /**
   * Restore from trash.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function restoreDelete(): NboxMetadataInterface;

  /**
   * Deletes the thread if all participants have set it to permanent delete.
   *
   * @return bool
   *   Has been deleted.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteWithCheck(): bool;

  /**
   * If the user is a sender in the thread.
   *
   * @return bool
   *   Sender.
   */
  public function getSender(): bool;

  /**
   * Get the sender summary, last 3 senders in thread plus message count.
   *
   * @param bool $relativeToUser
   *   Summary is relative to user (replace name with "me").
   * @param bool $senders
   *   If to build for senders or recipients.
   *
   * @return string
   *   Summary.
   */
  public function getSummary(bool $relativeToUser, bool $senders = TRUE): string;

  /**
   * Set the nbox metadata sender to true.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setSender(): NboxMetadataInterface;

  /**
   * If the user is a recipient in the thread.
   *
   * @return bool
   *   Recipient.
   */
  public function getRecipient(): bool;

  /**
   * Set the nbox metadata recipient to true.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setRecipient(): NboxMetadataInterface;

  /**
   * If the user starred the thread.
   *
   * @return bool
   *   Recipient.
   */
  public function getStarred(): bool;

  /**
   * Set the nbox metadata starred status.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setStarred(bool $starred): NboxMetadataInterface;

  /**
   * Toggle the starred status.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function toggleStarred(): NboxMetadataInterface;

  /**
   * Get the read/unread status.
   *
   * @return bool
   *   Read.
   */
  public function getRead(): bool;

  /**
   * Set the nbox metadata read status.
   *
   * @param bool $read
   *   Read.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setRead(bool $read): NboxMetadataInterface;

  /**
   * Thread is a draft.
   *
   * @return bool
   *   Draft.
   */
  public function isDraft(): bool;

  /**
   * Set the Nbox metadata draft status.
   *
   * @param bool $draft
   *   Draft.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setDraft(bool $draft): NboxMetadataInterface;

  /**
   * Get the most recent Nbox message ID.
   *
   * @return int
   *   Nbox message ID.
   */
  public function getMostRecentId(): int;

  /**
   * Get the most recent Nbox message.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   Nbox message.
   */
  public function getMostRecent(): NboxInterface;

  /**
   * Set the most recent nbox message ID.
   *
   * @param int $nboxId
   *   Nbox message ID.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setMostRecentId(int $nboxId): NboxMetadataInterface;

  /**
   * Get the total message count for the user in the thread.
   *
   * @return int
   *   Message count.
   */
  public function getMessageCount(): int;

  /**
   * If the user is a participant in a message has an attachment.
   *
   * @return bool
   *   Attachment.
   */
  public function hasAttachment(): bool;

  /**
   * Set TRUE the user is a participant in a message has an attachment.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function setAttachment(): NboxMetadataInterface;

  /**
   * Increment the total message count for the user in the thread.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function incrementMessageCount(): NboxMetadataInterface;

  /**
   * Update actions when someone replied.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   Nbox message.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  public function updateMetadataOnReply(NboxInterface $nbox): NboxMetadataInterface;

}
