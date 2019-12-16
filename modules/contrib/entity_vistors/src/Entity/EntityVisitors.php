<?php

namespace Drupal\entity_visitors\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Entity visitors entity.
 *
 * @ingroup entity_visitors
 *
 * @ContentEntityType(
 *   id = "entity_visitors",
 *   label = @Translation("Entity visitors"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\entity_visitors\EntityVisitorsListBuilder",
 *     "views_data" = "Drupal\entity_visitors\Entity\EntityVisitorsViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\entity_visitors\Form\EntityVisitorsForm",
 *       "add" = "Drupal\entity_visitors\Form\EntityVisitorsForm",
 *       "edit" = "Drupal\entity_visitors\Form\EntityVisitorsForm",
 *       "delete" = "Drupal\entity_visitors\Form\EntityVisitorsDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\entity_visitors\EntityVisitorsHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\entity_visitors\EntityVisitorsAccessControlHandler",
 *   },
 *   base_table = "entity_visitors",
 *   translatable = FALSE,
 *   admin_permission = "administer entity visitors entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/entity_visitors/{entity_visitors}",
 *     "add-form" = "/admin/structure/entity_visitors/add",
 *     "edit-form" = "/admin/structure/entity_visitors/{entity_visitors}/edit",
 *     "delete-form" = "/admin/structure/entity_visitors/{entity_visitors}/delete",
 *     "collection" = "/admin/structure/entity_visitors",
 *   },
 *   field_ui_base_route = "entity_visitors.settings"
 * )
 */
class EntityVisitors extends ContentEntityBase implements EntityVisitorsInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Visited Entity Name/Type'))
      ->setDescription(t('The name of the Entity visitors entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Entity visitors is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
