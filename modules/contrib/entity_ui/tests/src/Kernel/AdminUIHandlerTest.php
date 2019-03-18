<?php

namespace Drupal\Tests\field_ui\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests core entity types get the correct admin UI handlers set.
 *
 * @group entity_ui
 */
class AdminUIHandlerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  public static $modules = [
    'system',
    'user',
    // Needed for base fields on entities.
    'text',
    'node',
    'taxonomy',
    'field',
    'field_ui',
    'entity_ui',
  ];

  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['field', 'node', 'user', 'taxonomy']);
  }

  /**
   * Tests the Entity UI admin handlers on entity types.
   */
  public function testEntityUIAdminHandlers() {
    $entity_type_manager = $this->container->get('entity_type.manager');

    $expected_handlers = [
      // Entity type ID => expected handler class.
      'node' => \Drupal\entity_ui\EntityHandler\BundleEntityCollection::class,
      'user' => \Drupal\entity_ui\EntityHandler\BasicFieldUI::class,
      'taxonomy_term' => \Drupal\entity_ui\EntityHandler\BundleEntityCollection::class,
    ];

    foreach ($expected_handlers as $entity_type_id => $handler_class) {
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);

      $this->assertTrue($entity_type->hasHandlerClass('entity_ui_admin'), "The $entity_type_id entity type has a handler set.");
      $this->assertEqual($entity_type->getHandlerClass('entity_ui_admin'), $handler_class, "The $entity_type_id entity type has the $handler_class handler set.");
    }
  }

}
