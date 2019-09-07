<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the finance_supplier entity.
 *
 * @ingroup dfinance
 *
 * @ContentEntityType(
 *   id = "finance_supplier",
 *   label = @Translation("Supplier"),
 *   label_collection = @Translation("Manage Suppliers"),
 *   handlers = {
 *     "storage" = "Drupal\dfinance\Entity\Storage\SupplierStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dfinance\Controller\SupplierListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\dfinance\Form\SupplierForm",
 *       "add" = "Drupal\dfinance\Form\SupplierForm",
 *       "edit" = "Drupal\dfinance\Form\SupplierForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\dfinance\Access\SupplierAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\dfinance\Routing\SupplierHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "finance_supplier",
 *   data_table = "finance_supplier_field_data",
 *   revision_table = "finance_supplier_revision",
 *   revision_data_table = "finance_supplier_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer finance_supplier entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "trading_name" = "trading_name",
 *     "published" = "available",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/finance/supplier/{finance_supplier}",
 *     "add-form" = "/admin/finance/supplier/add",
 *     "edit-form" = "/admin/finance/supplier/{finance_supplier}/edit",
 *     "delete-form" = "/admin/finance/supplier/{finance_supplier}/delete",
 *     "version-history" = "/admin/finance/supplier/{finance_supplier}/revisions",
 *     "revision" = "/admin/finance/supplier/{finance_supplier}/revisions/{finance_supplier_revision}/view",
 *     "revision_revert" = "/admin/finance/supplier/{finance_supplier}/revisions/{finance_supplier_revision}/revert",
 *     "revision_delete" = "/admin/finance/supplier/{finance_supplier}/revisions/{finance_supplier_revision}/delete",
 *     "translation_revert" = "/admin/finance/supplier/{finance_supplier}/revisions/{finance_supplier_revision}/revert/{langcode}",
 *     "collection" = "/admin/finance/supplier",
 *   },
 *   field_ui_base_route = "entity.finance_supplier.collection"
 * )
 */
class Supplier extends RevisionableContentEntityBase implements SupplierInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the financial_doc owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log_message) || $record->revision_log_message === '')) {
      // If we are updating an existing entity without adding a new revision, we
      // need to make sure $entity->revision_log_message is reset whenever it is
      // empty.  Therefore, this code allows us to avoid clobbering an existing
      // log entry with an empty one.
      $record->revision_log_message = $this->original->revision_log_message->value;
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
  public function getTradingName() {
    return $this->get('trading_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTradingName($name) {
    $this->set('trading_name', $name);
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
    $fields = parent::baseFieldDefinitions($entity_type) +
      self::ownerBaseFieldDefinitions($entity_type) +
      self::publishedBaseFieldDefinitions($entity_type);

    /** @var \Drupal\Core\Field\BaseFieldDefinition $owner */
    $owner = $fields[$entity_type->getKey('owner')];
    $owner->setLabel(t('Authored by'))->setDescription(t('The author of this Supplier.'));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Legal Name'))
      ->setDescription(t('The legal name is the proper name of the supplier.  For individuals and companies this would be their full name including any suffixes (for example Limited, Ltd, Plc, GmbH).  This also does not need to be the name which the supplier trades as, in some cases this might be totally different to the supplier\'s trading name.  In most places across the system this is the name which will be used to identify the supplier.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
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

    $fields['trading_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Trading Name(s)'))
      ->setDescription(t('The trading name is the name that the supplier uses to trade as.  This field is optional and you can provide multiple trading names if the supplier trades under multiple different names.  Generally speaking this field is usually only used to make it easier to find the supplier when creating new documents (such as invoices), and should only ever be needed if the supplier\'s legal name is quite different from their trading name(s), however if their legal name is the same as or very similar to their trading name(s) then you likely don\'t need to complete this field.'))
      ->setRevisionable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
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
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    /** @var \Drupal\Core\Field\BaseFieldDefinition $published */
    $published = $fields[$entity_type->getKey('published')];
    $published
      ->setLabel(t('Available for use'))
      ->setDescription(t('Set whether this supplier is able to be chosen when creating or updating documents.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
