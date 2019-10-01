<?php

namespace Drupal\Tests\nbox\Kernel\Entity;

use Drupal\nbox\Entity\NboxMetadata;
use Drupal\nbox\Entity\NboxThread;
use Drupal\nbox\Entity\Nbox;

/**
 * Tests Nbox Metadata Entity.
 *
 * @coversDefaultClass \Drupal\nbox\Entity\NboxThread
 * @group nbox
 * @package Drupal\Tests\nbox\Kernel\Entity
 */
class NboxThreadTest extends NboxEntityKernelTestBase {

  /**
   * Test the autocreation of threads.
   */
  public function testTriggers() {
    $this->assertInternalType('null', $this->nbox->getThreadId());

    // Save the Nbox to the database.
    $this->setRecipients();
    $this->nbox->save();
    // Assert saving does not alter subject.
    $this->assertEquals('Lorem ipsum', $this->nbox->getSubject());

    // Make sure thread is created.
    $this->assertInternalType('int', $this->nbox->getThreadId());
    $this->assertInstanceOf(NboxThread::class, $this->nbox->getThread());
  }

  /**
   * Test base Nbox thread entity properties & fields.
   */
  public function testBaseNboxThread() {
    $this->setRecipients();
    $this->nbox->save();
    /** @var \Drupal\nbox\Entity\NboxThread $thread */
    $thread = $this->nbox->getThread();
    $this->assertEquals([$this->nbox->getOwnerId()], $thread->getSendersRecipientsInThread(0, TRUE));
    $this->assertEquals([
      $this->getUserId('bob'),
      $this->getUserId('carol'),
      $this->getUserId('dave'),
    ], $thread->getSendersRecipientsInThread(0, FALSE));

    $reply = $this->createReply($this->nbox, 'bob', 'Reply has subject');
    $reply->save();

    /** @var \Drupal\nbox\Entity\NboxThread $thread */
    $thread = $this->nbox->getThread();
    $messages = $thread->getMessages();
    $this->assertEquals($this->nbox->uuid(), Nbox::load($messages[0]['target_id'])->uuid());
    $this->assertEquals($reply->uuid(), Nbox::load($messages[1]['target_id'])->uuid());

    $messagesLoaded = $thread->getMessagesLoaded();
    $this->assertEquals($this->nbox->uuid(), reset($messagesLoaded)->uuid());
    $this->assertEquals($reply->uuid(), end($messagesLoaded)->uuid());

    $this->assertEquals($this->nbox->getSubject(), $thread->getThreadSubject());

    $this->assertEquals([$this->nbox->getOwnerId(), $this->getUserId('bob')], $thread->getSendersRecipientsInThread(0, TRUE));
    $this->assertEquals([$this->nbox->getOwnerId()], $thread->getSendersRecipientsInThread(1, TRUE));

    $storage = \Drupal::entityTypeManager()->getStorage('nbox');
    $messages = $storage->loadByThread($thread);
    $this->assertEquals($this->nbox->uuid(), reset($messages)->uuid());
    $this->assertEquals($reply->uuid(), end($messages)->uuid());
    $this->nbox->delete();
    $reply->delete();
    $this->assertNull($storage->loadByThread($thread));
  }

  /**
   * Test thread deletion and it's dependencies.
   */
  public function testNboxThreadDelete() {
    $this->nbox->save();
    $reply = $this->createReply($this->nbox, 'bob', 'Reply has subject');
    $reply->save();
    $messageId = $this->nbox->id();
    $replyId = $reply->id();

    $thread = $this->nbox->getThread();
    $threadId = $thread->id();

    $metadataStorage = \Drupal::entityTypeManager()->getStorage('nbox_metadata');
    $metadata = $metadataStorage->loadByParticipantsInThread($thread);
    $aliceId = reset($metadata)->id();
    $bobId = end($metadata)->id();

    $thread->delete();
    $this->assertNull(NboxThread::load($threadId));
    $this->assertNull(Nbox::load($messageId));
    $this->assertNull(Nbox::load($replyId));
    $this->assertNull(NboxMetadata::load($aliceId));
    $this->assertNull(NboxMetadata::load($bobId));
  }

}
