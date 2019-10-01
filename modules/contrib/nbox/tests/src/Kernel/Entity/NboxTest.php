<?php

namespace Drupal\Tests\nbox\Kernel\Entity;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\nbox\Entity\Nbox;
use Drupal\file\Entity\File;

/**
 * Tests Nbox Entity.
 *
 * @coversDefaultClass \Drupal\nbox\Entity\Nbox
 * @group nbox
 * @package Drupal\Tests\nbox\Kernel\Entity
 */
class NboxTest extends NboxEntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
  }

  /**
   * Test base Nbox entity properties & fields.
   */
  public function testBaseNbox() {
    // Test preCreate and owner.
    $this->assertEquals($this->getCurrentUser()->id(), $this->nbox->getOwnerId());
    $this->assertEquals($this->getCurrentUser(), $this->nbox->getOwner());

    // Test time.
    $this->assertTrue($this->isTimestamp($this->nbox->getSentTime()));

    // Test subject.
    $this->assertEquals('Lorem ipsum', $this->nbox->getSubject());
    $newSubject = 'dolar sit amet';
    $this->nbox->setSubject($newSubject);
    $this->assertEquals($newSubject, $this->nbox->getSubject());

    // Set the recipients.
    $this->setRecipients();
    $recipients = [
      'to' => [$this->getUserId('bob')],
      'cc' => [$this->getUserId('carol')],
      'bcc' => [$this->getUserId('dave')],
    ];
    $this->assertEquals($recipients, $this->nbox->getRecipients());

    // Test the participants.
    $participants = [
      $this->getUserId('alice'),
      $this->getUserId('bob'),
      $this->getUserId('carol'),
      $this->getUserId('dave'),
    ];
    $this->assertEquals($participants, $this->nbox->getParticipants());

    // Test relative time.
    $this->assertEquals(date(DateFormat::load('time_without_seconds')->getPattern(), REQUEST_TIME), $this->nbox->getSentTimeRelative());
    $yesterday = strtotime('yesterday');
    $this->nbox->set('sent', $yesterday);
    DateFormat::create([
      'id' => 'html_date',
      'pattern' => 'Y-m-d',
    ])->save();
    $this->assertEqual(date(DateFormat::load('html_date')->getPattern(), $yesterday), $this->nbox->getSentTimeRelative());

    // Test relative sender.
    $this->assertEquals('Me', $this->nbox->getSenderRelative());

    // Test relative recipients.
    $this->assertEqual([
      'to' => 'Bob',
      'cc' => 'Carol',
      'bcc' => 'Dave',
    ], $this->nbox->getRecipientsMarkup());
  }

  /**
   * Test Nbox replies.
   */
  public function testReply() {
    $this->setRecipients();
    $this->nbox->save();

    // Test replies.
    $reply = $this->createReply($this->nbox, 'bob', 'Reply');
    $reply->save();
    $this->assertEquals($this->nbox->id(), $reply->getParentId());
    $this->assertTrue($reply->isReply());
    // Assert saving does not set new reply subject with default setting.
    $this->assertEquals('Lorem ipsum', $reply->getSubject());

    // Test relative sender.
    $this->assertEquals('Bob', $reply->getSenderRelative());

    // Test replies with subject.
    \Drupal::configFactory()->getEditable('nbox.settings')->set('reply_has_subject', TRUE)->save();
    $reply = $this->createReply($this->nbox, 'bob', 'Reply has subject');
    $reply->save();
    $this->assertEquals('Reply has subject', $reply->getSubject());

    // Check metadata.
    $senderMetadata = $this->metadataStorage->loadBySender($this->nbox);
    $this->assertEquals($reply->id(), $senderMetadata->getMostRecentId());
    $this->assertEquals(3, $senderMetadata->getMessageCount());
    $this->assertFalse($senderMetadata->hasAttachment());

    // Replace owner.
    $reply->setOwnerId($this->getUserId('carol'));
    $this->assertEquals($this->getUserId('carol'), $reply->getOwnerId());
  }

  /**
   * Test Nbox reply all.
   */
  public function testReplyAll() {
    $this->setRecipients();
    $this->nbox->save();

    $reply = Nbox::replyToAll($this->nbox);
    $reply->setOwnerId($this->getUserId('bob'));
    $reply->setSubject('Reply subject');
    $reply->save();

    $this->assertEquals($this->nbox->id(), $reply->getParentId());
    $this->assertTrue($reply->isReply());
    // Assert saving does not set new reply subject with default setting.
    $this->assertEquals('Lorem ipsum', $reply->getSubject());

    // Test relative sender.
    $this->assertEquals('Bob', $reply->getSenderRelative());
    $this->assertEquals([
      'to' => [$this->getUserId('bob')],
      'cc' => [$this->getUserId('carol')],
    ], $reply->getRecipients());

    \Drupal::configFactory()->getEditable('nbox.settings')->set('reply_has_subject', TRUE)->save();
    $replyWithSubject = Nbox::replyToAll($this->nbox);
    $replyWithSubject->setOwnerId($this->getUserId('carol'));
    $replyWithSubject->save();
    $this->assertEquals('Re: Lorem ipsum', $replyWithSubject->getSubject());
  }

  /**
   * Test Nbox forward.
   */
  public function testForward() {
    $this->setRecipients();
    $this->nbox->save();

    $forward = Nbox::forward($this->nbox);
    $forward->set('field_nbox_to', $this->getUserId('dave'));
    $forward->setOwnerId($this->getUserId('bob'));
    $forward->setSubject('Forward subject');
    $forward->save();

    $this->assertEquals($this->nbox->id(), $forward->getParentId());
    $this->assertTrue($forward->forward);
    // Assert saving does not set new reply subject with default setting.
    $this->assertEquals('Lorem ipsum', $forward->getSubject());

    // Test relative sender.
    $this->assertEquals('Bob', $forward->getSenderRelative());
    $this->assertEquals(['to' => [$this->getUserId('dave')]], $forward->getRecipients());

    \Drupal::configFactory()->getEditable('nbox.settings')->set('reply_has_subject', TRUE)->save();
    $forwardWithSubject = Nbox::forward($this->nbox);
    $forwardWithSubject->setOwnerId($this->getUserId('carol'));
    $forwardWithSubject->set('field_nbox_to', $this->getUserId('bob'));
    $forwardWithSubject->save();
    $this->assertEquals('Fwd: Lorem ipsum', $forwardWithSubject->getSubject());
  }

  /**
   * Test attachments.
   */
  public function testAttachments() {
    // Test attachments.
    $this->assertFalse($this->nbox->hasAttachment());
    $this->nbox->save();
    $initialSenderMetadata = $this->metadataStorage->loadBySender($this->nbox);
    $this->assertFalse($initialSenderMetadata->hasAttachment());

    file_put_contents('public://example.txt', $this->randomMachineName());
    $attachment = File::create([
      'uri' => 'public://example.txt',
    ]);
    $attachment->save();

    $messageWithAttachment = Nbox::create([
      'type' => 'message',
      'subject' => 'Attachments',
    ])
      ->setOwner($this->getUser('carol'))
      ->set('field_nbox_to', $this->getUserId('alice'))
      ->set('field_nbox_attachment', $attachment);
    $messageWithAttachment->save();
    $this->assertTrue($messageWithAttachment->hasAttachment());
    // Check metadata.
    $senderMetadata = $this->metadataStorage->loadBySender($messageWithAttachment);
    $this->assertTrue($senderMetadata->hasAttachment());

    // Reply with attachment.
    $this->nbox->save();
    $values = [
      'subject' => 'Reply with attachment',
    ];
    $replyWithAttachment = Nbox::replyTo($this->nbox, $values)
      ->setOwner($this->getUser('bob'))
      ->set('field_nbox_to', $this->nbox->getOwnerId())
      ->set('field_nbox_attachment', $attachment);
    $replyWithAttachment->save();
    $this->assertTrue($messageWithAttachment->hasAttachment());
    $senderMetadata = $this->metadataStorage->loadBySender($this->nbox);
    $this->assertTrue($senderMetadata->hasAttachment());
  }

  /**
   * Test sending a mail to self.
   */
  public function testMailToSelf() {
    // Only one metadata entry should be created, so no "the thread & user
    // combination already exists" exception should be thrown.
    $messageToSelf = Nbox::create([
      'type' => 'message',
      'subject' => 'Hello me',
    ])
      ->setOwnerId($this->getUserId('alice'))
      ->set('field_nbox_to', $this->getUserId('alice'));
    $messageToSelf->save();

    /** @var \Drupal\nbox\Entity\NboxMetadata $senderMetadata */
    $senderMetadata = $this->metadataStorage->loadBySender($messageToSelf);
    $this->assertTrue($senderMetadata->getSender());
    $this->assertTrue($senderMetadata->getRecipient());
  }

}
