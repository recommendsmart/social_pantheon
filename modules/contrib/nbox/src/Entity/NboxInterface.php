<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Nbox entities.
 *
 * @ingroup nbox
 */
interface NboxInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the nbox subject.
   *
   * @return string
   *   Subject of the nbox.
   */
  public function getSubject(): string;

  /**
   * Sets the nbox subject.
   *
   * @param string $subject
   *   The nbox subject.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   The called nbox entity.
   */
  public function setSubject(string $subject): NboxInterface;

  /**
   * Gets the nbox sent timestamp.
   *
   * @return int
   *   Sent timestamp of the nbox.
   */
  public function getSentTime(): int;

  /**
   * Gets the nbox formatted sent timestamp relative to today.
   *
   * @return string
   *   Formatted sent timestamp of the nbox.
   */
  public function getSentTimeRelative(): string;

  /**
   * Creates a Nbox entity that is a reply to another message.
   *
   * @param Nbox $replyTo
   *   Nbox message replying to.
   * @param array $values
   *   Entity values.
   *
   * @return Nbox
   *   New Nbox.
   */
  public static function replyTo(Nbox $replyTo, array &$values): Nbox;

  /**
   * Creates a Nbox entity that is a reply to another message.
   *
   * @param Nbox $replyTo
   *   Nbox message replying to.
   * @param array $values
   *   Entity values.
   *
   * @return Nbox
   *   New Nbox.
   */
  public static function replyToAll(Nbox $replyTo, array &$values = []): Nbox;

  /**
   * Creates a Nbox entity that is a forward of another message.
   *
   * @param Nbox $forward
   *   Nbox message being forwarded.
   * @param array $values
   *   Entity values.
   *
   * @return Nbox
   *   New Nbox.
   */
  public static function forward(Nbox $forward, array &$values = []): Nbox;

  /**
   * Get the avatar with image style.
   *
   * @return string|null
   *   URL or NULL.
   */
  public function getOwnerAvatar(): ?string;

  /**
   * Get the relative user name for the sender.
   *
   * @return string
   *   Sender name.
   */
  public function getSenderRelative(): string;

  /**
   * Gets the nbox message thread ID.
   *
   * @return int
   *   The nbox message thread ID.
   */
  public function getThreadId(): ?int;

  /**
   * Gets the nbox message thread.
   *
   * @return int
   *   The nbox message thread.
   */
  public function getThread(): NboxThread;

  /**
   * Sets the nbox message thread.
   *
   * @param NboxThread $thread
   *   The nbox thread.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   The called nbox entity.
   */
  public function setThread(NboxThread $thread): NboxInterface;

  /**
   * Gets the nbox message delta.
   *
   * @return int
   *   The nbox message parent ID or NULL.
   */
  public function getDelta(): int;

  /**
   * Sets the nbox message delta.
   *
   * @param int $delta
   *   The nbox delta.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   The called nbox entity.
   */
  public function setDelta(int $delta): NboxInterface;

  /**
   * If the message is published.
   *
   * @return bool
   *   Published message status.
   */
  public function isPublished(): bool;

  /**
   * Set published message status.
   *
   * @param bool $published
   *   Published.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   The called nbox entity.
   */
  public function setPublished(bool $published): NboxInterface;

  /**
   * If the nbox message is a reply.
   *
   * @return bool
   *   Is reply.
   */
  public function isReply(): bool;

  /**
   * Gets the nbox message parent ID.
   *
   * @return int|null
   *   The nbox message parent ID or NULL.
   */
  public function getParentId(): ?int;

  /**
   * Gets the nbox message parent.
   *
   * @return \Drupal\nbox\Entity\NboxInterface|null
   *   The nbox parent message or null.
   */
  public function getParent(): ?NboxInterface;

  /**
   * Sets the nbox message parent ID.
   *
   * @param \Drupal\nbox\Entity\NboxInterface $nbox
   *   The nbox parent.
   *
   * @return \Drupal\nbox\Entity\NboxInterface
   *   The called nbox entity.
   */
  public function setParent(NboxInterface $nbox): NboxInterface;

  /**
   * Get all the get all the recipient fields in the nbox message.
   *
   * @return array
   *   Recipient field names.
   */
  public function getRecipientFields(): array;

  /**
   * Get the recipients in a message.
   *
   * @return array
   *   Recipient user ID's per recipient field type.
   */
  public function getRecipientsWithBcc(): array;

  /**
   * Get the recipients in a message the user is allowed to see.
   *
   * @return array
   *   Recipient user ID's per recipient field type.
   */
  public function getRecipients(): array;

  /**
   * Get the recipients in a message the user is allowed to see.
   *
   * @return array
   *   Recipient user ID's.
   */
  public function getRecipientsFlat(): array;

  /**
   * Get all the recipients names in a message the user is allowed to see.
   *
   * @return array
   *   Recipient names per recipient field type.
   */
  public function getRecipientsMarkup(): array;

  /**
   * Get all recipients plus sender.
   *
   * @return array
   *   Participant user ID's.
   */
  public function getParticipants(): array;

  /**
   * If the nbox message has an attachment.
   *
   * @return bool
   *   Attachment.
   */
  public function hasAttachment(): bool;

}
