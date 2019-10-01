<?php

namespace Drupal\Tests\nbox\Kernel\Storage;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Tests\nbox\Kernel\Entity\NboxEntityKernelTestBase;
use Drupal\nbox\Entity\Nbox;
use Drupal\nbox\Entity\NboxMetadata;

/**
 * Tests Nbox Metadata Storage.
 *
 * @coversDefaultClass \Drupal\nbox\Entity\Storage\NboxMetadataStorage
 * @group nbox
 * @package Drupal\Tests\nbox\Kernel\Entity
 */
class NboxMetadataStorageTest extends NboxEntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setRecipients();
    $this->nbox->save();
  }

  /**
   * Test the base storage methods.
   */
  public function testStorage() {
    $senderMetadata = $this->metadataStorage->loadBySender($this->nbox);
    $this->assertInstanceOf(NboxMetadata::class, $senderMetadata);

    // Make sure metadata is created with correct defaults for a single
    // recipient.
    $recipientMetadata = $this->metadataStorage->loadByParticipant($this->nbox, $this->getUser('bob'));
    $this->assertInstanceOf(NboxMetadata::class, $recipientMetadata);
    $this->assertTrue($recipientMetadata->getRecipient());
    $this->assertFalse($recipientMetadata->getSender());
    $this->assertFalse($recipientMetadata->getRead());

    // Test metadata per participant.
    /** @var \Drupal\nbox\Entity\NboxMetadataInterface $participantMetadata */
    $participantMetadata = $this->metadataStorage->loadByParticipantInThread($this->getUser('bob'), $this->nbox->getThread());
    $this->assertEquals($this->metadataStorage->loadByParticipant($this->nbox, $this->getUser('bob')), $participantMetadata);

    // Test messages without Metadata.
    $senderMetadata->delete();
    $this->assertNull($this->metadataStorage->loadBySender($this->nbox));
    $recipientMetadata->delete();
    $this->assertNull($this->metadataStorage->loadByParticipant($this->nbox, $this->getUser('bob')));
    $this->assertNull($this->metadataStorage->loadByParticipantInThread($this->getUser('bob'), $this->nbox->getThread()));

    // Test unsaved message.
    $unsaved = Nbox::create([
      'type' => 'message',
      'subject' => 'Unsaved',
    ])
      ->setOwner($this->getUser('carol'))
      ->set('field_nbox_to', $this->getUserId('alice'));
    $this->expectException(EntityStorageException::class);
    $this->metadataStorage->loadBySender($unsaved);
  }

}
