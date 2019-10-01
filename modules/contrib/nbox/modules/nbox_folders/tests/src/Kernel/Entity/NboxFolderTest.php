<?php

namespace Drupal\Tests\nbox_folder\Kernel\Entity;

use Drupal\nbox_folders\Entity\NboxFolder;
use Drupal\Tests\nbox\Kernel\Entity\NboxEntityKernelTestBase;

/**
 * Tests Nbox Folder Entity.
 *
 * @coversDefaultClass \Drupal\nbox_folders\Entity\NboxFolder
 * @group nbox
 * @package Drupal\Tests\nbox_folder\Kernel\Entity
 */
class NboxFolderTest extends NboxEntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['nbox', 'file', 'field', 'user', 'nbox_folders'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['nbox_folders']);
    $this->installEntitySchema('nbox_folder');
  }

  /**
   * Test base Nbox Folder entity properties & fields.
   */
  public function testFolderBase() {
    $folder = NboxFolder::create();
    $folder->setOwner($this->getCurrentUser());

    $name = 'My folder';
    $folder->setName($name);
    $this->assertEquals($name, $folder->getName());

    $this->assertTrue($this->isTimestamp($folder->getCreatedTime()));

    $this->assertEquals($this->getCurrentUser()->id(), $folder->getOwnerId());

    $folder->setOwnerId($this->getUserId('bob'));
    $this->assertEquals($this->getUser('bob'), $folder->getOwner());
  }

}
