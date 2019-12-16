<?php

namespace Drupal\Tests\modal_page\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\modal_page\ModalPage;

/**
 * KernelTests for ModalPage service class.
 *
 * @group modal_page
 */
class ModalPageTest extends KernelTestBase {

  /**
   * The Modal Page service.
   *
   * @var \Drupal\modal_page\ModalPage
   */
  protected $modalPage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'modal_page',
  ];

  /**
   * The setUp.
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['modal_page']);
    $this->languageManager = $this->container->get('language_manager');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->configFactory = $this->container->get('config.factory');
    $this->database = $this->container->get('database');
    $this->requestStack = $this->container->get('request_stack');
    $this->pathMatcher = $this->container->get('path.matcher');
    $this->uuid = $this->container->get('uuid');
    $this->currentUser = $this->container->get('current_user');

    $this->modalPage = new ModalPage(
      $this->languageManager, $this->entityTypeManager, $this->configFactory,
      $this->database, $this->requestStack, $this->pathMatcher,
      $this->uuid, $this->currentUser
    );
  }

  /**
   * Test ModalPage::getCurrentPath().
   */
  public function testCurrentPath() {
    $this->assertSame(
      $this->modalPage->getCurrentPath(),
      ltrim($this->requestStack->getCurrentRequest()->getRequestUri(), '/')
    );
  }

}
