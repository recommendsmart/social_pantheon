<?php

namespace Drupal\Tests\nbox\Kernel\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\nbox\Entity\NboxMetadata;
use Drupal\nbox\Entity\NboxThread;
use Drupal\nbox\Entity\NboxMetadataInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Tests Nbox Metadata Entity.
 *
 * @coversDefaultClass \Drupal\nbox\Entity\NboxMetadata
 * @group nbox
 * @package Drupal\Tests\nbox\Kernel\Entity
 */
class NboxMetadataTest extends NboxEntityKernelTestBase {

  /**
   * Sender Nbox metadata.
   *
   * @var \Drupal\nbox\Entity\NboxMetadata
   */
  protected $senderMetadata;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setRecipients();
    $this->nbox->save();
    $this->senderMetadata = $this->metadataStorage->loadBySender($this->nbox);
  }

  /**
   * Test the presave function.
   */
  public function testPreSave() {
    // Create a duplicate thread & uid pair.
    $duplicate = NboxMetadata::create([
      'nbox_thread_id' => $this->senderMetadata->getThreadId(),
      'uid' => $this->senderMetadata->getOwnerId(),
    ]);
    $this->expectException(EntityStorageException::class);
    $duplicate->save();
  }

  /**
   * Test base Nbox entity properties & fields.
   */
  public function testBaseNboxMetadata() {
    $this->senderMetadata->save();
    // Make sure metadata is created with correct defaults for sender.
    $this->assertInstanceOf(NboxMetadata::class, $this->senderMetadata);
    $this->assertTrue($this->senderMetadata->getSender());
    $this->assertFalse($this->senderMetadata->getRecipient());
    $this->assertTrue($this->senderMetadata->getRead());
    $this->assertEquals($this->getCurrentUser()->id(), $this->senderMetadata->getOwnerId());
    $this->assertEquals($this->getCurrentUser(), $this->senderMetadata->getOwner());
    $this->assertEquals('not_deleted', $this->senderMetadata->getDeleteStatus());
    $this->assertEquals($this->nbox->id(), $this->senderMetadata->getMostRecentId());
    $this->assertEquals($this->nbox->uuid(), $this->senderMetadata->getMostRecent()->uuid());
    $this->assertEquals(1, $this->senderMetadata->getMessageCount());
    $this->assertFalse($this->senderMetadata->hasAttachment());

    // Test threads.
    $this->assertInternalType('int', $this->senderMetadata->getThreadId());
    $this->assertInstanceOf(NboxThread::class, $this->senderMetadata->getThread());

    // Make sure metadata is created with correct defaults for recipients.
    $recipientsMetadata = $this->metadataStorage->loadByRecipients($this->nbox);
    foreach ($recipientsMetadata as $recipientMetadata) {
      $this->assertInstanceOf(NboxMetadata::class, $recipientMetadata);
      $this->assertTrue($recipientMetadata->getRecipient());
      $this->assertFalse($recipientMetadata->getSender());
      $this->assertFalse($recipientMetadata->getRead());
    }

    // Assert early exit of deleteWithCheck().
    $this->assertFalse($this->senderMetadata->deleteWithCheck());

    // Use reflection to access private method.
    $reflection = new \ReflectionObject($this->senderMetadata);
    $reflectionMethod = $reflection->getMethod('setDeleteStatus');
    $reflectionMethod->setAccessible(TRUE);
    // Update deleted status.
    // Don't allow an unknown status.
    $reflectionMethod->invoke($this->senderMetadata, $this->randomMachineName());
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_FALSE, $this->senderMetadata->getDeleteStatus());

    $this->senderMetadata->markDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_MARKED, $this->senderMetadata->getDeleteStatus());

    $this->senderMetadata->restoreDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_FALSE, $this->senderMetadata->getDeleteStatus());

    // Restoring a second time should do nothing.
    $this->senderMetadata->restoreDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_FALSE, $this->senderMetadata->getDeleteStatus());

    $this->senderMetadata->markDelete();
    $this->senderMetadata->markDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_PERMANENT, $this->senderMetadata->getDeleteStatus());

    // Delete while already deleted should do nothing.
    $this->senderMetadata->markDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_PERMANENT, $this->senderMetadata->getDeleteStatus());

    // Restoring when permanently deleted should do nothing.
    $this->senderMetadata->restoreDelete();
    $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_PERMANENT, $this->senderMetadata->getDeleteStatus());

    // Test starring thread.
    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $this->assertFalse($this->senderMetadata->getStarred());
    $this->senderMetadata->toggleStarred();
    $this->assertTrue($this->senderMetadata->getStarred());
    $this->assertCount(1, $messenger->messagesByType(MessengerInterface::TYPE_STATUS));
    $this->senderMetadata->toggleStarred();
    $this->assertFalse($this->senderMetadata->getStarred());
    $this->assertCount(2, $messenger->messagesByType(MessengerInterface::TYPE_STATUS));

    $this->senderMetadata->setStarred(TRUE);
    $this->assertTrue($this->senderMetadata->getStarred());
    $this->senderMetadata->setStarred(FALSE);
    $this->assertFalse($this->senderMetadata->getStarred());

    $this->assertFalse($this->senderMetadata->deleteWithCheck());

    // Test deletion.
    foreach ($recipientsMetadata as $recipientMetadata) {
      $recipientMetadata->markDelete();
      $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_MARKED, $recipientMetadata->getDeleteStatus());
      $recipientMetadata->markDelete();
      $this->assertEquals(NboxMetadataInterface::NBOX_DELETE_PERMANENT, $recipientMetadata->getDeleteStatus());
      $recipientMetadata->save();
    }

    $thread = $this->senderMetadata->getThread();
    $this->assertInstanceOf(NboxThread::class, $thread);

    $this->assertTrue($this->senderMetadata->deleteWithCheck());
    $metadata = $this->metadataStorage->loadBySender($this->nbox);
    $this->assertNull($metadata);
  }

  /**
   * Test the summary.
   */
  public function testGetSummary() {
    $summaryGeneral = $this->senderMetadata->getSummary(FALSE);
    $this->assertEquals('Alice', $summaryGeneral);

    $summaryGeneral = $this->senderMetadata->getSummary(TRUE);
    $this->assertEquals('Me', $summaryGeneral);

    $recipients = $this->nbox->getRecipients();
    foreach ($recipients as $recipientIds) {
      foreach ($recipientIds as $key => $recipient) {
        /** @var \Drupal\nbox\Entity\Nbox $reply */
        $reply = $this->createReply($this->nbox, $this->getUserName($recipient), 'Reply');
        $reply->set('sent', REQUEST_TIME + $key + 1);
        $reply->save();
      }
    }
    /** @var \Drupal\nbox\Entity\NboxMetadata $updatedSenderMetadata */
    $updatedSenderMetadata = $this->metadataStorage->loadBySender($this->nbox);
    $summaryGeneral = $updatedSenderMetadata->getSummary(TRUE);
    $this->assertEquals('Bob, Carol, Dave...', $summaryGeneral);
  }

  /**
   * Test the metadata reply status.
   */
  public function testReplyMetadata() {
    $reply = $this->createReply($this->nbox, 'bob', 'Reply');
    $reply->save();
    $replyMetadata = $this->metadataStorage->loadBySender($reply);
    $this->assertTrue($replyMetadata->getSender());
    $this->assertTrue($replyMetadata->getRead());

    $replySelf = $this->createReply($this->nbox, 'alice', 'Reply self');
    $replySelf->save();
    $replySelfMetadata = $this->metadataStorage->loadBySender($reply);
    $this->assertTrue($replySelfMetadata->getRecipient());
  }

}
