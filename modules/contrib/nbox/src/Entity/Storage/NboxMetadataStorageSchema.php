<?php

namespace Drupal\nbox\Entity\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines the Nbox schema handler.
 */
class NboxMetadataStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    if ($storage = $this->storage->getBaseTable()) {

      $schema[$storage]['unique keys'] += [
        'nbox_metadata__thread_uid' => ['nbox_thread_id', 'uid'],
      ];
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name === 'nbox_metadata') {
      switch ($field_name) {
        case 'nbox_thread_id':
          $schema['fields'][$field_name]['not null'] = TRUE;
          break;

        case 'deleted':
          $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
          break;

      }
    }

    return $schema;
  }

}
