<?php

namespace Drupal\nbox_folders\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\nbox\Entity\NboxMetadataInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Nbox folder entity.
 *
 * @ingroup nbox_folders
 *
 * @ContentEntityType(
 *   id = "nbox_folder",
 *   label = @Translation("Nbox folder"),
 *   label_singular = @Translation("Nbox folder"),
 *   label_plural = @Translation("Nbox folders"),
 *   label_count = @PluralTranslation(
 *     singular = "@count nbox folder",
 *     plural = "@count nbox folders"
 *   ),
 *   label_collection = @Translation("Nbox folders"),
 *   handlers = {
 *     "views_data" = "Drupal\nbox_folders\Entity\Views\NboxFolderViewsData",
 *     "access" = "Drupal\nbox_folders\Entity\Access\NboxFolderAccessControlHandler",
 *     "storage" = "Drupal\nbox_folders\Entity\Storage\NboxFolderStorage",
 *     "route_provider" = {
 *       "html" = "Drupal\nbox_folders\Entity\Routing\NboxFolderRouteProvider",
 *     },
 *   },
 *   base_table = "nbox_folder",
 *   admin_permission = "administer nbox folder",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   field_ui_base_route = "nbox_folder.settings"
 * )
 */
class NboxFolder extends ContentEntityBase implements NboxFolderInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    /** @var \Drupal\nbox\Entity\Storage\NboxMetadataStorage $metadataStorage */
    $metadataStorage = \Drupal::entityTypeManager()->getStorage('nbox_metadata');
    foreach ($entities as $folder) {
      // Make sure we remove all folders from the metadata.
      $metadata = $metadataStorage->loadByProperties([
        'folder' => $folder->id(),
        'uid' => $folder->getOwnerId(),
      ]);
      /** @var \Drupal\nbox\Entity\NboxMetadata $result */
      foreach ($metadata as $nboxMetadata) {
        $nboxMetadata->set('folder', NULL);
        $nboxMetadata->save();
      }
    }
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
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function moveMetadataToFolder(NboxMetadataInterface $nboxMetadata): NboxMetadataInterface {
    $nboxMetadata->set('folder', $this->id());
    $nboxMetadata->save();
    return $nboxMetadata;
  }

  /**
   * Remove the metadata thread object from a folder.
   *
   * @param \Drupal\nbox\Entity\NboxMetadataInterface $nboxMetadata
   *   Nbox metadata object.
   *
   * @return \Drupal\nbox\Entity\NboxMetadataInterface
   *   Nbox metadata object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function removeMetadataFolder(NboxMetadataInterface $nboxMetadata): NboxMetadataInterface {
    $nboxMetadata->set('folder', NULL);
    $nboxMetadata->save();
    return $nboxMetadata;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Nbox folder entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
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
      ->setDescription(t('The name of the Nbox folder entity.'))
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

    return $fields;
  }

}
