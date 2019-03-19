<?php

/**
 * @file
 * Contains \Drupal\redhen_liability\Entity\Liability.
 */

namespace Drupal\redhen_liability\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\redhen_liability\LiabilityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Liability entity.
 *
 * @ingroup redhen_liability
 *
 * @ContentEntityType(
 *   id = "redhen_liability",
 *   label = @Translation("Liability"),
 *   label_singular = @Translation("liability"),
 *   label_plural = @Translation("liabilities"),
 *   label_count = @PluralTranslation(
 *     singular = "@count liability",
 *     plural = "@count liability",
 *   ),
 *   bundle_label = @Translation("Liability type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\redhen_liability\LiabilityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\redhen_liability\Form\LiabilityForm",
 *       "add" = "Drupal\redhen_liability\Form\LiabilityForm",
 *       "edit" = "Drupal\redhen_liability\Form\LiabilityForm",
 *       "delete" = "Drupal\redhen_liability\Form\LiabilityDeleteForm",
 *     },
 *     "access" = "Drupal\redhen_liability\LiabilityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\redhen_liability\LiabilityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "redhen_liability",
 *   revision_table = "redhen_liability_revision",
 *   admin_permission = "administer liability entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/redhen/liability/{redhen_liability}",
 *     "add-form" = "/redhen/liability/add/{redhen_liability_type}",
 *     "edit-form" = "/redhen/liability/{redhen_liability}/edit",
 *     "delete-form" = "/redhen/liability/{redhen_liability}/delete",
 *     "collection" = "/redhen/liability",
 *   },
 *   bundle_entity_type = "redhen_liability_type",
 *   field_ui_base_route = "entity.redhen_liability_type.edit_form"
 * )
 */
class Liability extends ContentEntityBase implements LiabilityInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getName();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    $name = $this->get('name')->value;
    // Allow other modules to alter the name of the org.
    \Drupal::moduleHandler()->alter('redhen_liability_name', $name, $this);
    return $name;
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
  public function getType() {
    return $this->bundle();
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
  public function isActive() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', $active ? REDHEN_LIABILITY_INACTIVE : REDHEN_LIABILITY_ACTIVE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the liability.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -10,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRevisionable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDescription(t('A boolean indicating whether the liability is active.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
        'weight' => 16,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the liability was created.'))
      ->setRevisionable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the liability was last edited.'))
      ->setRevisionable(TRUE);

    return $fields;
  }

}
