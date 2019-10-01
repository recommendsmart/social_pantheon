<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Defines the Nbox entity.
 *
 * @ingroup nbox
 *
 * @ContentEntityType(
 *   id = "nbox",
 *   label = @Translation("Nbox message"),
 *   label_singular = @Translation("Nbox message"),
 *   label_plural = @Translation("Nbox messages"),
 *   label_count = @PluralTranslation(
 *     singular = "@count nbox message",
 *     plural = "@count nbox messages"
 *   ),
 *   label_collection = @Translation("Nbox"),
 *   bundle_label = @Translation("Nbox type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "storage_schema" = "Drupal\nbox\Entity\Storage\NboxStorageSchema",
 *     "storage" = "Drupal\nbox\Entity\Storage\NboxStorage",
 *     "access" = "Drupal\nbox\Entity\Access\NboxAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\nbox\Entity\Routing\NboxRouteProvider",
 *     },
 *   },
 *   base_table = "nbox",
 *   admin_permission = "administer nbox entities",
 *   entity_keys = {
 *     "id" = "nbox_id",
 *     "bundle" = "type",
 *     "label" = "subject",
 *     "uuid" = "uuid",
 *     "uid" = "sender",
 *   },
 *   bundle_entity_type = "nbox_type",
 *   field_ui_base_route = "entity.nbox_type.edit_form"
 * )
 */
class Nbox extends ContentEntityBase implements NboxInterface {

  use NboxRelativityTrait;

  /**
   * Set new for postSave.
   *
   * @var bool
   */
  protected $new;

  /**
   * Get storage for the Nbox Metadata entity.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   Entity Storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function nboxMetadataStorage(): EntityStorageInterface {
    return $this->entityTypeManager()->getStorage('nbox_metadata');
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    if (!isset($values['sender'])) {
      $values += [
        'sender' => \Drupal::currentUser()->id(),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->new = FALSE;

    // New message that is not a reply.
    if ($this->isNew() && $this->getParent() === NULL && $this->getThreadId() === NULL) {
      // Create the thread.
      $thread = $this->entityTypeManager()
        ->getStorage('nbox_thread')
        ->create();
      $thread->save();
      $this->setThread($thread);

      // Set the new value.
      $this->new = TRUE;
    }

    // New message that is a reply.
    if ($this->isReply() && $this->isPublished()) {
      // Overwrite delta from ::replyTo as others might have made a reply
      // faster.
      $delta = $this->getParent()->getDelta() + 1;
      $this->setDelta($delta);

      // If replies have no subject, then set original subject.
      $config = \Drupal::config('nbox.settings');
      if (!$config->get('reply_has_subject')) {
        $this->setSubject($this->getParent()->getSubject());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Metadata should only be created if the message is new, or a new user has
    // been added to the thread.
    // It should only updated when the message is a reply.
    // If the Nbox entity is somehow updated, this should not trigger any
    // metadata updates.
    $recipients = [];
    if (count(array_values($this->getRecipientsWithBcc())) > 0) {
      $recipients = array_merge(...array_values($this->getRecipientsWithBcc()));
    }

    $participants = $this->getParticipants();

    if ($this->isPublished()) {
      $this->ensureParticipantMetadata($participants, $recipients);
    }
    // Save or update draft.
    else {
      $senderMetadata = $this->nboxMetadataStorage()->loadBySender($this);
      if ($senderMetadata instanceof NboxMetadata) {
        $this->updateMetadata($senderMetadata, [$this->getOwnerId()]);
      }
      else {
        // If the message is not published only create metadata for sender.
        $this->createMetadata($this->getOwnerId(), $recipients);
      }
    }
  }

  /**
   * Make sure metadata is either created or updated.
   *
   * @param array $participants
   *   Participants.
   * @param array $recipients
   *   Recipients.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function ensureParticipantMetadata(array $participants, array $recipients) {
    $participantsMetadata = $this->nboxMetadataStorage()->loadByParticipants($this);
    foreach ($participants as $participant) {
      if (in_array($participant, array_keys($participantsMetadata))) {
        // Update the metadata.
        $this->updateMetadata($participantsMetadata[$participant], $recipients);
      }
      else {
        // Create the metadata.
        $this->createMetadata($participant, $recipients);
      }
    }
  }

  /**
   * Update metadata on reply.
   *
   * @param \Drupal\nbox\Entity\NboxMetadata $participantsMetadata
   *   Metadata.
   * @param array $recipients
   *   Recipients.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateMetadata(NboxMetadata $participantsMetadata, array $recipients) {
    if ($this->isReply()) {
      // Update metadata for sender.
      $participantsMetadata->updateMetadataOnReply($this);
    }

    if ($this->getOwnerId() === $participantsMetadata->getOwnerId()) {
      $participantsMetadata->setSender();
      $participantsMetadata->setRead(TRUE);
      // Sender is also recipient.
      if (in_array($participantsMetadata->getOwnerId(), $recipients)) {
        $participantsMetadata->setRecipient();
      }
    }
    // Update metadata for recipient.
    else {
      $participantsMetadata->setRecipient();
      $participantsMetadata->setRead(FALSE);
    }
    $participantsMetadata->setDraft(!$this->isPublished());
    $participantsMetadata->save();
  }

  /**
   * Create metadata for message.
   *
   * @param int $participant
   *   Participant ID.
   * @param array $recipients
   *   Recipients.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createMetadata(int $participant, array $recipients) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $metadata */
    // Set the metadata for the sender.
    if ($participant === $this->getOwnerId()) {
      $metadata = $this->nboxMetadataStorage()->createFromNbox($this, $this->getOwnerId());
      $metadata->setSender();
      $metadata->setRead(TRUE);
      if (in_array($this->getOwnerId(), $recipients)) {
        $metadata->setRecipient();
      }
    }
    else {
      $metadata = $this->nboxMetadataStorage()->createFromNbox($this, $participant);
      $metadata->setRecipient();
    }

    $metadata->setDraft(!$this->isPublished());
    $metadata->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function replyTo(Nbox $replyTo, array &$values = []): Nbox {
    $values['type'] = $replyTo->bundle();
    $reply = self::create($values);
    $config = \Drupal::config('nbox.settings');
    $prefix = '';
    if ($config->get('reply_has_subject')) {
      $prefix = 'Re: ';
    }
    $reply->setSubject($prefix . $replyTo->getSubject());
    $reply->setPublished(TRUE);

    $reply->setParent($replyTo);
    $reply->set('field_nbox_to', $replyTo->getOwnerId());
    $reply->setThread($replyTo->getThread());
    // Temp delta.
    $reply->setDelta($replyTo->getDelta() + 1);
    return $reply;
  }

  /**
   * {@inheritdoc}
   */
  public static function replyToAll(Nbox $replyTo, array &$values = []): Nbox {
    $values['type'] = $replyTo->bundle();
    $reply = self::create($values);
    $config = \Drupal::config('nbox.settings');
    $prefix = '';
    if ($config->get('reply_has_subject')) {
      $prefix = 'Re: ';
    }
    $reply->setSubject($prefix . $replyTo->getSubject());
    $reply->setPublished(TRUE);
    $reply->setParent($replyTo);
    $currentUser = \Drupal::currentUser()->id();
    $sender = $replyTo->getOwnerId() == $currentUser;
    foreach ($replyTo->getRecipientFields() as $recipientField) {
      $recipients = [];
      if ($target = $replyTo->get($recipientField['name'])->getValue()) {
        if ($recipientField['recipient_type'] === 'to') {
          $recipients = array_column($target, 'target_id');
          // Add the sender as recipient.
          if ($currentUser != $replyTo->getOwnerId()) {
            array_unshift($recipients, $replyTo->getOwnerId());
          }
        }
        elseif ($recipientField['recipient_type'] === 'bcc') {
          foreach (array_column($target, 'target_id') as $uid) {
            // Only the sender or the bcc recipient should be able to see bcc.
            if ($sender || $uid == $currentUser) {
              $recipients[] = $uid;
            }
          }
        }
        else {
          $recipients = array_column($target, 'target_id');
        }
        if (($key = array_search($currentUser, $recipients)) !== FALSE) {
          unset($recipients[$key]);
        }
        $reply->set($recipientField['name'], $recipients);
      }
    }

    $reply->setThread($replyTo->getThread());
    // Temp delta.
    $reply->setDelta($replyTo->getDelta() + 1);
    return $reply;
  }

  /**
   * {@inheritdoc}
   */
  public static function forward(Nbox $forwardMessage, array &$values = []): Nbox {
    $values['type'] = $forwardMessage->bundle();
    $forward = self::create($values);
    $config = \Drupal::config('nbox.settings');
    $prefix = '';
    if ($config->get('reply_has_subject')) {
      $prefix = 'Fwd: ';
    }
    $forward->setSubject($prefix . $forwardMessage->getSubject());
    $forward->setPublished(TRUE);
    $forward->setParent($forwardMessage);

    $forward->setThread($forwardMessage->getThread());
    // Temp delta.
    $forward->setDelta($forwardMessage->getDelta() + 1);
    $forward->forward = TRUE;
    return $forward;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubject(): string {
    return $this->get('subject')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject(string $subject): NboxInterface {
    $this->set('subject', $subject);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSentTime(): int {
    return $this->get('sent')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSentTimeRelative(): string {
    return $this->dateToRelative($this->getSentTime());
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('sender')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId(): int {
    return $this->get('sender')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerAvatar(): ?string {
    $configStore = \Drupal::config('nbox.settings');
    if ($configStore->get('message_has_avatar') && !$this->getOwner()->user_picture->isEmpty()) {
      $style = $configStore->get('message_avatar_style');
      $path = $this->getOwner()->user_picture->entity->getFileUri();
      return ImageStyle::load($style)->buildUrl($path);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($sender) {
    $this->set('sender', $sender);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('sender', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSenderRelative(): string {
    return $this->relativeUserName($this->getOwnerId());
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
  public function getThreadId(): ?int {
    return $this->get('nbox_thread_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setThread(NboxThread $thread): NboxInterface {
    $this->set('nbox_thread_id', $thread->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDelta(): int {
    return $this->get('delta')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDelta(int $delta): NboxInterface {
    $this->set('delta', $delta);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished(): bool {
    return (bool) $this->get('published')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished(bool $published): NboxInterface {
    $this->set('published', $published);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isReply(): bool {
    return ($this->getParentId() > 0 && $this->getDelta() > 0);
  }

  /**
   * {@inheritdoc}
   */
  public function getParentId(): ?int {
    return $this->get('pid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getParent(): ?NboxInterface {
    return $this->get('pid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setParent(NboxInterface $nbox): NboxInterface {
    $this->set('pid', $nbox->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['sender'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Sender'))
      ->setDescription(t('The user ID of user sending nbox message.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['subject'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setDescription(t('Subject of the message.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['sent'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Sent'))
      ->setDescription(t('The time that the message was sent.'));

    $fields['pid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent nbox ID'))
      ->setDescription(t('The parent nbox ID if this is a reply.'))
      ->setSetting('target_type', 'nbox')
      ->setDefaultValue(0);

    $fields['delta'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Delta'))
      ->setDescription(t('The delta of the message within the thread.'))
      ->setDefaultValue(0);

    $fields['published'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published'))
      ->setDescription(t('If not published the message is not sent / a draft.'))
      ->setDefaultValue(TRUE);

    $fields['nbox_thread_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Thread ID'))
      ->setDescription(t('The thread this message is a part of.'))
      ->setSetting('target_type', 'nbox_thread')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipientFields(): array {
    $recipientFields = [];
    $fields = $this->getFieldDefinitions();
    /** @var \Drupal\field\Entity\FieldConfig $field */
    foreach ($fields as $field_name => $field) {
      if ($field->getType() === 'recipient') {
        $recipientFields[$field_name] = [
          'name' => $field_name,
          'recipient_type' => $field->getSetting('handler_settings')['recipient_type'],
        ];
      }
    }
    return $recipientFields;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipientsWithBcc(): array {
    $recipients = [];
    $recipientFields = $this->getRecipientFields();
    foreach ($recipientFields as $recipientField) {
      if ($target = $this->get($recipientField['name'])->getValue()) {
        $recipients[$recipientField['recipient_type']] = array_column($target, 'target_id');
      }
    }
    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipients(): array {
    $currentUser = \Drupal::currentUser()->id();
    $recipients = [];
    $recipientsBcc = $this->getRecipientsWithBcc();
    foreach ($recipientsBcc as $fieldname => $recipientIds) {
      if ($fieldname === 'bcc') {
        foreach ($recipientIds as $uid) {
          // Only the sender or the bcc recipient should be able to see bcc.
          if ($this->getOwnerId() == $currentUser || $uid === $currentUser) {
            $recipients[$fieldname][] = $uid;
          }
        }
      }
      else {
        $recipients[$fieldname] = $recipientIds;
      }
    }
    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipientsFlat(): array {
    $recipients = [];
    if (count(array_values($this->getRecipients())) > 0) {
      $recipients = array_merge(...array_values($this->getRecipients()));
    }
    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipientsMarkup(): array {
    $recipientsMarkup = [];
    foreach ($this->getRecipients() as $recipient_type => $recipients) {
      $recipientsMarkup[$recipient_type] = implode(', ', $this->relativeUserNameMultiple($recipients));
    }
    return $recipientsMarkup;
  }

  /**
   * {@inheritdoc}
   */
  public function getParticipants(): array {
    $participants = $this->getRecipientsFlat();
    array_unshift($participants, $this->getOwnerId());
    return array_unique($participants);
  }

  /**
   * {@inheritdoc}
   */
  public function hasAttachment(): bool {
    $fields = $this->getFieldDefinitions();
    foreach ($fields as $field_name => $field) {
      if ($field->getType() === 'file' && !$this->get($field_name)->isEmpty()) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
