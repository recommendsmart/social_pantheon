<?php

namespace Drupal\Tests\nbox\Kernel\Entity\Controller;

use Drupal\nbox\Entity\Controller\NboxTypeListBuilder;
use Drupal\nbox\Entity\NboxType;
use Drupal\Tests\nbox\Kernel\Entity\NboxEntityKernelTestBase;

/**
 * Tests Nbox type List builder.
 *
 * @coversDefaultClass \Drupal\nbox\Entity\Controller\NboxTypeListBuilder
 * @group nbox
 * @package Drupal\Tests\nbox\Kernel\Entity\Controller
 */
class NboxTypeListBuilderTest extends NboxEntityKernelTestBase {

  /**
   * Tests Nbox type List builder.
   */
  public function testBaseNbox() {
    $nboxType = NboxType::load('message');

    $storage = \Drupal::entityTypeManager()->getStorage('nbox_type');
    $nboxTypeList = new NboxTypeListBuilder($nboxType->getEntityType(), $storage);

    $header = $nboxTypeList->buildHeader();
    $row = $nboxTypeList->buildRow($nboxType);
    $columns = ['label', 'id'];
    foreach ($columns as $column) {
      $this->assertArrayHasKey($column, $header);
      $this->assertArrayHasKey($column, $row);
    }
  }

}
