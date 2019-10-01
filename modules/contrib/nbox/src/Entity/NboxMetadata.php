<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;

/**
 * Defines the Nbox metadata entity.
 *
 * @ingroup nbox
 *
 * @ContentEntityType(
 *   id = "nbox_metadata",
 *   label = @Translation("Nbox metadata"),
 *   handlers = {
 *     "access" = "Drupal\nbox\Entity\Access\NboxMetadataAccessControlHandler",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\nbox\Entity\Views\NboxMetadataViewsData",
 *     "storage" = "Drupal\nbox\Entity\Storage\NboxMetadataStorage",
 *     "storage_schema" = "Drupal\nbox\Entity\Storage\NboxMetadataStorageSchema",
 *   },
 *   base_table = "nbox_metadata",
 *   admin_permission = "administer nbox metadata entities",
 *   entity_keys = {
 *     "id" = "nbox_metadata_id",
 *     "uid" = "uid",
 *     "uuid" = "uuid",
 *   },
 *   constraints = {
 *     "UniqueThreadUser" = {}
 *   }
 * )
 */
class NboxMetadata extends ContentEntityBase implements NboxMetadataInterface {

  use NboxRelativityTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Validate the entity.
    if ($this->isNew()) {
      $violations = $this->validate();
      if ($violations->count() > 0) {
        throw new EntityStorageException($violations[0]->getMessage());
      }
    }

    parent::preSave($storage);
  }

  /**
   * The possible delete statuses.
   *
   * @return array
   *   Possible delete statuses.
   */
  public static function getDeleteStatuses(): array {
    return [
      NboxMetadataInterface::NBOX_DELETE_FALSE,
      NboxMetadataInterface::NBOX_DELETE_MARKED,
      NboxMetadataInterface::NBOX_DELETE_PERMANENT,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getThread(): NboxThread {
    return $this->get('nbox_thread_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getThreadId(): int {
    return $this->get('nbox_thread_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner(): UserInterface {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId(): int {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeleteStatus(): string {
    return $this->get('deleted')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function markDelete(): NboxMetadataInterface {
    switch ($this->getDeleteStatus()) {
      case NboxMetadataInterface::NBOX_DELETE_FALSE:
        $this->setDeleteStatus(NboxMetadataInterface::NBOX_DELETE_MARKED);
        break;

      case NboxMetadataInterface::NBOX_DELETE_MARKED:
        $this->setDeleteStatus(NboxMetadataInterface::NBOX_DELETE_PERMANENT);
        break;
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function restoreDelete(): NboxMetadataInterface {
    if ($this->getDeleteStatus() === NboxMetadataInterface::NBOX_DELETE_MARKED) {
      $this->setDeleteStatus(NboxMetadataInterface::NBOX_DELETE_FALSE);
    }
    return $this;
  }

  /**
   * Set the nbox metadata delete status.
   *
   * @param string $deleteStatus
   *   The delete status.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  private function setDeleteStatus(string $deleteStatus): NboxMetadataInterface {
    if (in_array($deleteStatus, $this->getDeleteStatuses())) {
      $this->set('deleted', $deleteStatus);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteWithCheck(): bool {
    if ($this->getDeleteStatus() === NboxMetadataInterface::NBOX_DELETE_PERMANENT) {
      $thread = $this->getThread();
      $storage = $this->entityTypeManager()->getStorage('nbox_metadata');
      $participantsMetadata = $storage->loadByParticipantsInThread($thread);
      foreach ($participantsMetadata as $metadata) {
        // We don't check the current status again, this allows us to get test
        // coverage on the function.
        if ($this->uuid() !== $metadata->uuid() && $metadata->getDeleteStatus() !== NboxMetadataInterface::NBOX_DELETE_PERMANENT) {
          return FALSE;
        }
      }
      $thread->delete();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSender(): bool {
    return $this->get('sender')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSender(): NboxMetadataInterface {
    $this->set('sender', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipient(): bool {
    return $this->get('recipient')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRecipient(): NboxMetadataInterface {
    $this->set('recipient', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStarred(): bool {
    return $this->get('starred')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function toggleStarred(): NboxMetadataInterface {
    if ($this->getStarred()) {
      $this->setStarred(FALSE);
      $message = 'Unstarred thread.';
    }
    else {
      $this->setStarred(TRUE);
      $message = 'Starred thread.';
    }
    \Drupal::messenger()->addMessage($message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStarred(bool $starred): NboxMetadataInterface {
    $this->set('starred', $starred);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRead(): bool {
    return $this->get('read')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRead(bool $read): NboxMetadataInterface {
    $this->set('read', $read);
    Cache::invalidateTags(['unread_list:' . $this->getOwnerId()]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isDraft(): bool {
    return (bool) $this->get('draft')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDraft(bool $draft): NboxMetadataInterface {
    $this->set('draft', $draft);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMostRecentId(): int {
    return $this->get('most_recent')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getMostRecent(): NboxInterface {
    return $this->get('most_recent')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setMostRecentId(int $nboxId): NboxMetadataInterface {
    $this->set('most_recent', $nboxId);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessageCount(): int {
    return $this->get('message_count')->value;
  }

  /**
   * Set the total message count for the user in the thread.
   *
   * @param int $count
   *   Count.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   The called nbox metadata entity.
   */
  private function setMessageCount(int $count): NboxMetadataInterface {
    $this->set('message_count', $count);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasAttachment(): bool {
    return $this->get('attachment')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAttachment(): NboxMetadataInterface {
    $this->set('attachment', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function incrementMessageCount(): NboxMetadataInterface {
    $messageCount = $this->getMessageCount() + 1;
    $this->setMessageCount($messageCount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function updateMetadataOnReply(NboxInterface $nbox): NboxMetadataInterface {
    $this->setMostRecentId($nbox->id());
    $this->incrementMessageCount();
    if ($nbox->hasAttachment()) {
      $this->setAttachment();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary(bool $relativeToUser, bool $senders = TRUE): string {
    if ($senders === FALSE || $this->getMessageCount() > 1) {
      $userIds = $this->getThread()->getSendersRecipientsInThread(3, $senders);
    }
    else {
      $userIds[] = $this->getMostRecent()->getOwnerId();
    }

    $names = [];
    foreach ($userIds as $userId) {
      if ($relativeToUser) {
        $names[] = $this->relativeUserName($userId);
      }
      else {
        $names[] = User::load($userId)->getDisplayName();
      }
    }
    $result = implode(', ', $names);
    if ($this->getMessageCount() > 3) {
      $result .= '...';
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['nbox_thread_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('nbox thread id'))
      ->setDescription(t('The thread ID of the metadata entity.'))
      ->setSetting('target_type', 'nbox_thread')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the nbox metadata entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE);

    $status = self::getDeleteStatuses();
    $defaultStatus = reset($status);
    $fields['deleted'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Deleted'))
      ->setDescription(t('The deleted status the nbox metadata entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue($defaultStatus)
      ->setRequired(TRUE);

    $fields['delete_changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Delete changed'))
      ->setDescription(t('The time that the delete status was created.'))
      ->setRequired(TRUE);

    $fields['read'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Read'))
      ->setDescription(t('A boolean indicating whether the thread has been read.'))
      ->setDefaultValue(FALSE)
      ->setRequired(TRUE);

    $fields['draft'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Draft'))
      ->setDescription(t('If thread is a draft.'))
      ->setDefaultValue(FALSE);

    $fields['recipient'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Recipient'))
      ->setDescription(t('A boolean indicating if the user is a recipient of one of the messages in the thread.'))
      ->setDefaultValue(FALSE)
      ->setRequired(TRUE);

    $fields['sender'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sender'))
      ->setDescription(t('A boolean indicating if the user is a sender of one of the messages in the thread.'))
      ->setDefaultValue(FALSE)
      ->setRequired(TRUE);

    $fields['starred'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Starred'))
      ->setDescription(t('A boolean indicating if the user has starred one of the messages in the thread.'))
      ->setDefaultValue(FALSE)
      ->setRequired(TRUE);

    $fields['message_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Message count'))
      ->setDescription(t('The number of messages this thread contains for the user.'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    $fields['most_recent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Most recent'))
      ->setDescription(t('The most recent message in this thread for the user.'))
      ->setSetting('target_type', 'nbox')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE);

    $fields['attachment'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Attachment'))
      ->setDescription(t('A boolean indicating if the thread has an attachment in one of the messages.'));

    return $fields;
  }

}
