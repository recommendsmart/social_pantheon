<?php

namespace Drupal\Tests\nbox\Kernel\Entity;

use Drupal\nbox\Entity\Nbox;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Defines an abstract test base for nbox entity kernel tests.
 */
abstract class NboxEntityKernelTestBase extends EntityKernelTestBase {

  /**
   * Users that message each other.
   *
   * @var array
   */
  private $users;

  /**
   * Nbox message.
   *
   * @var \Drupal\nbox\Entity\Nbox
   */
  protected $nbox;

  /**
   * Metadata storage container.
   *
   * @var \Drupal\nbox\Entity\Storage\NboxMetadataStorage
   */
  protected $metadataStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['nbox', 'file', 'field', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['nbox']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('nbox');
    $this->installEntitySchema('nbox_type');
    $this->installEntitySchema('nbox_metadata');
    $this->installEntitySchema('nbox_thread');

    $this->users = [
      'alice' => $this->createUser(['name' => 'Alice'])->id(),
      'bob' => $this->createUser(['name' => 'Bob'])->id(),
      'carol' => $this->createUser(['name' => 'Carol'])->id(),
      'dave' => $this->createUser(['name' => 'Dave'])->id(),
    ];

    $this->setCurrentUserId($this->users['alice']);

    $this->nbox = Nbox::create([
      'type' => 'message',
      'subject' => 'Lorem ipsum',
    ]);

    $entityTypeManager = $this->container->get('entity_type.manager');
    $this->metadataStorage = $entityTypeManager->getStorage('nbox_metadata');
  }

  /**
   * Sets the current user so Nbox entities can rely on it.
   *
   * @param int $uid
   *   User ID.
   *
   * @throws \Exception
   */
  protected function setCurrentUserId(int $uid) {
    $account = User::load($uid);
    $this->container->get('current_user')->setAccount($account);
  }

  /**
   * Gets the current user so Nbox can run checks against them.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   Current user.
   */
  protected function getCurrentUser(): AccountInterface {
    return \Drupal::currentUser()->getAccount();
  }

  /**
   * Get user.
   *
   * @param string $user
   *   User name.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   User.
   */
  protected function getUser(string $user): AccountInterface {
    return User::load($this->users[$user]);
  }

  /**
   * Get user Id.
   *
   * @param string $user
   *   User name.
   *
   * @return int
   *   User ID.
   */
  protected function getUserId(string $user): int {
    return $this->users[$user];
  }

  /**
   * Get user Id.
   *
   * @param int $userId
   *   User name.
   *
   * @return string
   *   User name.
   */
  protected function getUserName(int $userId): string {
    return array_search($userId, $this->users);
  }

  /**
   * Test if is timestamp.
   *
   * @param int|string $timestamp
   *   Timestamp.
   *
   * @return bool
   *   Is timestamp.
   */
  protected function isTimestamp($timestamp): bool {
    return ctype_digit($timestamp) && strtotime(date('Y-m-d H:i:s', $timestamp)) === (int) $timestamp;
  }

  /**
   * Set the recipients.
   */
  protected function setRecipients(): void {
    $this->nbox->set('field_nbox_to', $this->getUserId('bob'));
    $this->nbox->set('field_nbox_cc', $this->getUserId('carol'));
    $this->nbox->set('field_nbox_bcc', $this->getUserId('dave'));
  }

  /**
   * Create a test reply.
   *
   * @param \Drupal\nbox\Entity\Nbox $replyTo
   *   Message replying to.
   * @param string $userName
   *   User name.
   * @param string $subject
   *   Subject.
   *
   * @return \Drupal\nbox\Entity\Nbox
   *   Unsaved reply.
   */
  protected function createReply(Nbox $replyTo, string $userName, string $subject): Nbox {
    // Test replies.
    $reply = Nbox::replyTo($replyTo)
      ->setOwner($this->getUser($userName))
      ->set('field_nbox_to', $this->nbox->getOwnerId());
    $reply->setSubject($subject);
    return $reply;
  }

}
