<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the Nbox schema handler.
 */
class NboxStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    if ($storage = $this->storage->getBaseTable()) {
      $schema[$storage]['indexes'] += [
        'nbox__thread_order' => ['nbox_thread_id', 'delta'],
      ];
    }

    return $schema;
  }

}
