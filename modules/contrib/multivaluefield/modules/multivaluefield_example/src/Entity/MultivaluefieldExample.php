<?php

namespace Drupal\multivaluefield_example\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Defines the Paragraph entity.
 *
 * @ingroup multivaluefield
 *
 * @ContentEntityType(
 *   id = "multivaluefield_example",
 *   label = @Translation("Multivaluefield Example"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "multivaluefield_example",
 *   data_table = "multivaluefield_example_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class MultivaluefieldExample extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the entity.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the entity.'))
      ->setReadOnly(TRUE);

    //MVF Single
    $fields['mvf'] = BaseFieldDefinition::create('multivaluefield')
      ->setLabel(t('Key-Value'))
      ->setSetting('fields_count', 2)
      ->setSetting('index_field_name', 'Index')
      ->setSetting('fields', array(
        0 => array(
          'name' => 'Key',
          'type' => 'basicfield_text',
        ),
        1 => array(
          'name' => 'Value',
          'type' => 'basicfield_text',
        ),
      ))
      ->setDescription(t('Example for Key-Value multi value field.'))
      ->setDisplayOptions('view', [
        'display_type' => 'table',
        'display_field_label' => 1,
      ])
      ->setDisplayOptions('form', [
        'label_type' => 'placeholder',
      ])
      ->setReadOnly(TRUE);

    //MVF Multiple
    $fields['mvf_multi'] = BaseFieldDefinition::create('multivaluefield')
      ->setLabel(t('Key-Value'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('fields_count', 2)
      ->setSetting('index_field_name', 'Index')
      ->setSetting('fields', array(
        0 => array(
          'name' => 'Key',
          'type' => 'basicfield_text',
        ),
        1 => array(
          'name' => 'Value',
          'type' => 'basicfield_text',
        ),
      ))
      ->setDescription(t('Example for Key-Value multi value field.'))
      ->setReadOnly(TRUE);


    return $fields;
  }

}
