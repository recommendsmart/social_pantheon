<?php

namespace Drupal\Tests\modal_page\Unit;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Session\AccountProxy;
use Drupal\Tests\UnitTestCase;
use Drupal\modal_page\ModalPage;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\AliasManager;

/**
 * Tests for ModalPage class.
 *
 * @group modal_page
 */
class ModalPageTest extends UnitTestCase {

  /**
   * Initial setUp to tests.
   */
  public function setUp() {
    parent::setUp();
    $this->languageManager = $this->createMock(LanguageManager::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManager::class);
    $this->configFactory = $this->getConfigFactoryStub([
      'modal_page.settings' => [
        'no_modal_page_external_js' => TRUE,
        'allowed_tags' => "h1,h2,a,b,big,code,del,em,i,ins,pre,q,small,span,strong,sub,sup,tt,ol,ul,li,p,br,img",
      ],
    ]);
    $this->database = $this->createMock(Connection::class);
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->pathMatcher = $this->createMock(PathMatcher::class);
    $this->uuid = $this->createMock(UuidInterface::class);
    $this->currentUser = $this->createMock(AccountProxy::class);
    $this->aliasManager = $this->createMock(AliasManager::class);

    $this->modalPage = new ModalPage(
      $this->languageManager, $this->entityTypeManager, $this->configFactory,
      $this->database, $this->requestStack, $this->pathMatcher,
      $this->uuid, $this->currentUser, $this->aliasManager
    );
  }

  /**
   * Test ModalPage::clearText().
   */
  public function testClearText() {
    $this->assertEquals($this->modalPage->clearText('  This is a test!'), 'This is a test!');
  }

  /**
   * Test ModalPage::getAllowTags().
   */
  public function testAllowTags() {
    $this->assertCount(24, $this->modalPage->getAllowTags());
    $this->assertContains('h2', $this->modalPage->getAllowTags());
  }

}
