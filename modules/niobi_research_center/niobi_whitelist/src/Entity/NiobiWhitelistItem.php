<?php

namespace Drupal\niobi_whitelist\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Whitelist Item entity.
 *
 * @ingroup niobi_whitelist
 *
 * @ContentEntityType(
 *   id = "niobi_whitelist_item",
 *   label = @Translation("Whitelist Item"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\niobi_whitelist\NiobiWhitelistItemListBuilder",
 *     "views_data" = "Drupal\niobi_whitelist\Entity\NiobiWhitelistItemViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\niobi_whitelist\Form\NiobiWhitelistItemForm",
 *       "add" = "Drupal\niobi_whitelist\Form\NiobiWhitelistItemForm",
 *       "edit" = "Drupal\niobi_whitelist\Form\NiobiWhitelistItemForm",
 *       "delete" = "Drupal\niobi_whitelist\Form\NiobiWhitelistItemDeleteForm",
 *     },
 *     "access" = "Drupal\niobi_whitelist\NiobiWhitelistItemAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\niobi_whitelist\NiobiWhitelistItemHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "niobi_whitelist_item",
 *   admin_permission = "administer whitelist item entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/niobi/niobi_whitelist_item/{niobi_whitelist_item}",
 *     "add-form" = "/admin/niobi/niobi_whitelist_item/add",
 *     "edit-form" = "/admin/niobi/niobi_whitelist_item/{niobi_whitelist_item}/edit",
 *     "delete-form" = "/admin/niobi/niobi_whitelist_item/{niobi_whitelist_item}/delete",
 *     "collection" = "/admin/niobi/niobi_whitelist_item",
 *   },
 *   field_ui_base_route = "niobi_whitelist_item.settings"
 * )
 */
class NiobiWhitelistItem extends ContentEntityBase implements NiobiWhitelistItemInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Whitelist Item entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Whitelist Item entity.'))
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
