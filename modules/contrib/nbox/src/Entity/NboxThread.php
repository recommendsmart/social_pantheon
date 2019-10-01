<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\nbox\Entity\Field\ThreadMessages;

/**
 * Defines the Nbox thread entity.
 *
 * @ingroup nbox
 *
 * @ContentEntityType(
 *   id = "nbox_thread",
 *   label = @Translation("Nbox thread"),
 *   handlers = {
 *     "access" = "Drupal\nbox\Entity\Access\NboxThreadAccessControlHandler",
 *     "view_builder" = "Drupal\nbox\Entity\ViewBuilder\NboxThreadViewBuilder",
 *     "list_builder" = "Drupal\nbox\Entity\Controller\NboxThreadListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\nbox\Entity\Form\NboxThreadDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\nbox\Entity\Routing\NboxThreadRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/nbox/{nbox_thread}",
 *     "delete-form" = "/admin/nbox/{nbox_thread}/delete",
 *     "collection" = "/admin/nbox",
 *   },
 *   base_table = "nbox_thread",
 *   admin_permission = "administer nbox entities",
 *   entity_keys = {
 *     "id" = "nbox_thread_id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class NboxThread extends ContentEntityBase implements NboxThreadInterface {

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    $messageStorage = \Drupal::entityTypeManager()->getStorage('nbox');
    $metadataStorage = \Drupal::entityTypeManager()->getStorage('nbox_metadata');
    foreach ($entities as $thread) {
      // Make sure we delete all messages and metadata relating to the thread.
      $messages = $thread->getMessagesLoaded(FALSE);
      $messageStorage->delete($messages);

      $metadata = $metadataStorage->loadByParticipantsInThread($thread);
      $metadataStorage->delete($metadata);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMessages(): ?array {
    return $this->get('messages')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getMessagesLoadedWithBcc(): ?array {
    $messageIds = $this->get('messages')->getValue();
    return Nbox::loadMultiple(array_column($messageIds, 'target_id'));
  }

  /**
   * {@inheritdoc}
   */
  public function getMessagesLoaded(): ?array {
    $messages = $this->getMessagesLoadedWithBcc();
    /** @var \Drupal\nbox\Entity\Nbox $message */
    foreach ($messages as $id => $message) {
      if (!$message->access('view')) {
        unset($messages[$id]);
      }
    }
    return $messages;
  }

  /**
   * {@inheritdoc}
   */
  public function getThreadSubject(): string {
    $messages = $this->getMessages();
    return Nbox::load(array_column($messages, 'target_id')[0])->getSubject();
  }

  /**
   * {@inheritdoc}
   */
  public function getSendersRecipientsInThread(int $limit, bool $senders): array {
    // Watch https://www.drupal.org/node/2913224 for DI in entities.
    $connection = \Drupal::database();
    $query = $connection->select('nbox', 'n');
    $query->condition('n.nbox_thread_id', $this->id());
    if ($limit > 0) {
      $query->range(0, $limit);
    }
    // If senders return data from column.
    if ($senders) {
      $query->fields('n', ['sender']);
      $query->orderBy('n.sent', 'DESC');
      return $query->execute()->fetchCol();
    }

    // If recipients get from recipient fields.
    $recipients = [[]];
    $query->fields('n', ['nbox_id']);
    if ($result = $query->execute()->fetchCol()) {
      $messages = Nbox::loadMultiple($result);
      foreach ($messages as $message) {
        $recipients[] = $message->getRecipientsFlat();
      }
    }
    $recipients = array_merge(...$recipients);
    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['messages'] = BaseFieldDefinition::create('entity_reference')
      ->setName('messages')
      ->setSetting('target_type', 'nbox')
      ->setLabel(t('Messages'))
      ->setComputed(TRUE)
      ->setClass(ThreadMessages::class);

    return $fields;
  }

}
