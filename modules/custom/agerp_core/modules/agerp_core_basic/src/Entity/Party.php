<?php

namespace Drupal\agerp_core_basic\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\agerp_core\EntityOwnerTrait;
use Drupal\agerp_core_basic\PartyInterface;
use Drupal\entity\Revision\RevisionableContentEntityBase;

/**
 * AGERP Party Entity Class.
 *
 * @ContentEntityType(
 *   id = "agerp_core_party",
 *   label = @Translation("Party"),
 *   bundle_label = @Translation("Party type"),
 *   handlers = {
 *     "access" = "Drupal\agerp_core_basic\PartyAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\agerp_core_basic\Form\PartyForm",
 *       "delete" = "Drupal\agerp_core_basic\Form\PartyDeleteForm",
 *     },
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\agerp_core_basic\PartyListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *       "revision" = "\Drupal\entity\Routing\RevisionRouteProvider",
 *     },
 *     "local_task_provider" = {
 *       "default" = "\Drupal\agerp_core_basic\Menu\BasicLocalTaskProvider",
 *     },
 *   },
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer agerp_core_party entities",
 *   base_table = "agerp_core_party",
 *   revision_table = "agerp_core_party_revision",
 *   entity_keys = {
 *     "id" = "party_id",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "agerp_core_party_type",
 *   field_ui_base_route = "entity.agerp_core_party_type.edit_form",
 *   permission_granularity = "bundle",
 *   permission_labels = {
 *     "singular" = @Translation("Party"),
 *     "plural" = @Translation("Parties"),
 *   },
 *   links = {
 *     "add-page" = "/agerp-core/party/add",
 *     "add-form" = "/agerp-core/party/add/{agerp_core_party_type}",
 *     "canonical" = "/agerp-core/party/{agerp_core_party}",
 *     "collection" = "/agerp-core/party",
 *     "edit-form" = "/agerp-core/party/{agerp_core_party}/edit",
 *     "delete-form" = "/agerp-core/party/{agerp_core_party}/delete",
 *     "revision" = "/agerp-core/party/{agerp_core_party}/revisions/{agerp_core_party_revision}/view",
 *     "revision-revert-form" = "/agerp-core/party/{agerp_core_party}/revisions/{agerp_core_party_revision}/revert",
 *     "version-history" = "/agerp-core/party/{agerp_core_party}/revisions",
 *   }
 * )
 */
class Party extends RevisionableContentEntityBase implements PartyInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the party was created.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the party was last edited.'))
      ->setRevisionable(TRUE);

    $fields['uid'] = EntityOwnerTrait::getOwnerFieldDefinition()
      ->setDescription(t('The user that is the party owner.'));

    $fields['name'] = BaseFieldDefinition::create('name')
      ->setLabel(t('Name'))
      ->setDescription(t('Name of the party.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'name_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'name_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Gets the primary address.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\Drupal\Core\TypedData\TypedDataInterface
   *   The address property object.
   */
  public function getPrimaryAddress() {
    return $this->getPrimaryField('address');
  }

  /**
   * Gets the primary email.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\Drupal\Core\TypedData\TypedDataInterface
   *   The email property object.
   */
  public function getPrimaryEmail() {
    return $this->getPrimaryField('email');
  }

  /**
   * Gets the primary phone.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\Drupal\Core\TypedData\TypedDataInterface
   *   The phone property object.
   */
  public function getPrimaryPhone() {
    return $this->getPrimaryField('phone');
  }

  /**
   * Gets the primary field.
   *
   * @param string $field
   *   The primary field name.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|\Drupal\Core\TypedData\TypedDataInterface
   *   The primary field property object.
   *
   * @throws \InvalidArgumentException
   *   If no primary field is configured.
   *   If the configured primary field does not exist.
   */
  public function getPrimaryField($field) {
    $type = $this->get('type')->entity;
    $name = empty($type->primary_fields[$field]) ? '' : $type->primary_fields[$field];
    return $this->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = '';
    if ($item = $this->get('name')->first()) {
      $label = "$item->given $item->family";
    }
    if (empty(trim($label))) {
      $label = t('Nameless #@id', ['@id' => $this->id()]);
    }
    \Drupal::moduleHandler()->alter('agerp_core_party_label', $label, $this);

    return $label;
  }

}
