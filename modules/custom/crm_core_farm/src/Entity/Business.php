<?php

namespace Drupal\crm_core_farm\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\crm_core\EntityOwnerTrait;
use Drupal\crm_core_farm\FarmInterface;
use Drupal\entity\Revision\RevisionableContentEntityBase;

/**
 * CRM Business Entity Class.
 *
 * @ContentEntityType(
 *   id = "crm_core_business",
 *   label = @Translation("CRM Core Business"),
 *   bundle_label = @Translation("Business type"),
 *   handlers = {
 *     "access" = "Drupal\crm_core_farm\BusinessAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\crm_core_farm\Form\BusinessForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\crm_core_farm\BusinessListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *       "revision" = "\Drupal\entity\Routing\RevisionRouteProvider",
 *     },
 *   },
 *   base_table = "crm_core_business",
 *   revision_table = "crm_core_business_revision",
 *   admin_permission = "administer crm_core_business entities",
 *   show_revision_ui = TRUE,
 *   entity_keys = {
 *     "id" = "business_id",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "crm_core_business_type",
 *   field_ui_base_route = "entity.crm_core_business_type.edit_form",
 *   permission_granularity = "bundle",
 *   permission_labels = {
 *     "singular" = @Translation("Business"),
 *     "plural" = @Translation("Business"),
 *   },
 *   links = {
 *     "add-page" = "/crm-core/business/add",
 *     "add-form" = "/crm-core/business/add/{crm_core_business_type}",
 *     "canonical" = "/crm-core/business/{crm_core_business}",
 *     "collection" = "/crm-core/business",
 *     "edit-form" = "/crm-core/business/{crm_core_business}/edit",
 *     "delete-form" = "/crm-core/business/{crm_core_business}/delete",
 *     "revision" = "/crm-core/business/{crm_core_business}/revisions/{crm_core_business_revision}/view",
 *     "revision-revert-form" = "/crm-core/business/{crm_core_business}/revisions/{crm_core_business_revision}/revert",
 *     "version-history" = "/crm-core/business/{crm_core_business}/revisions",
 *   }
 * )
 */
class Business extends RevisionableContentEntityBase implements FarmInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the business was created.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the business was last edited.'))
      ->setRevisionable(TRUE);

    $fields['uid'] = EntityOwnerTrait::getOwnerFieldDefinition()
      ->setDescription(t('The user that is the business owner.'));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
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
    $name = empty($type->getPrimaryFields()[$field]) ? '' : $type->getPrimaryFields()[$field];
    return $this->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = $this->get('name')->value;
    if (empty($label)) {
      $label = t('Nameless #@id', ['@id' => $this->id()]);
    }
    \Drupal::moduleHandler()->alter('crm_core_business_label', $label, $this);

    return $label;
  }

}
