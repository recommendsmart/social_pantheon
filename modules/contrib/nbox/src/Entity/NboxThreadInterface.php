<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for defining Nbox thread entities.
 *
 * @ingroup nbox
 */
interface NboxThreadInterface extends ContentEntityInterface {

  /**
   * Gets the messages for a thread.
   *
   * @return array|null
   *   An array of target id's.
   */
  public function getMessages(): ?array;

  /**
   * Gets the loaded messages for a thread.
   *
   * @return \Drupal\nbox\Entity\Nbox[]|null
   *   An array of target id's.
   */
  public function getMessagesLoadedWithBcc(): ?array;

  /**
   * Gets the loaded messages for a thread that the user is allowed to see.
   *
   * @return \Drupal\nbox\Entity\Nbox[]|null
   *   An array of target id's.
   */
  public function getMessagesLoaded(): ?array;

  /**
   * Get the subject of the first message in a thread.
   *
   * @return string
   *   Subject.
   */
  public function getThreadSubject(): string;

  /**
   * Get all the senders or participants in the thread.
   *
   * @param int $limit
   *   Limit the number of senders.
   * @param bool $senders
   *   Senders or participants.
   *
   * @return array
   *   Sender UID's.
   */
  public function getSendersRecipientsInThread(int $limit, bool $senders): array;

}
