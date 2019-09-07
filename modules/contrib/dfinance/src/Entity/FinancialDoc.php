<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Financial Document entity.
 *
 * @ingroup dfinance
 *
 * @ContentEntityType(
 *   id = "financial_doc",
 *   label = @Translation("Financial Document"),
 *   label_collection = @Translation("All Financial Documents"),
 *   bundle_label = @Translation("Financial Document type"),
 *   handlers = {
 *     "storage" = "Drupal\dfinance\Entity\Storage\FinancialDocStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "collection" = "Drupal\dfinance\Controller\FinancialDocCollection",
 *     "collection-for-organisation" = "Drupal\dfinance\Controller\FinancialDocCollection",
 *
 *     "form" = {
 *       "default" = "Drupal\dfinance\Form\FinancialDocForm",
 *       "add" = "Drupal\dfinance\Form\FinancialDocForm",
 *       "edit" = "Drupal\dfinance\Form\FinancialDocForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\dfinance\Access\FinancialDocAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\dfinance\Routing\FinancialDocHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "financial_doc",
 *   data_table = "financial_doc_field_data",
 *   revision_table = "financial_doc_revision",
 *   revision_data_table = "financial_doc_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer financial document entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/finance/document/{financial_doc}",
 *     "add-page" = "/finance/document/add",
 *     "add-page-for-organisation" = "/finance/{finance_organisation}/document/add",
 *     "add-form" = "/finance/document/add/{financial_doc_type}",
 *     "add-form-for-organisation" = "/finance/{finance_organisation}/document/add/{financial_doc_type}",
 *     "edit-form" = "/finance/document/{financial_doc}/edit",
 *     "delete-form" = "/finance/document/{financial_doc}/delete",
 *     "version-history" = "/finance/document/{financial_doc}/revisions",
 *     "revision" = "/finance/document/{financial_doc}/revisions/{financial_doc_revision}/view",
 *     "revision_revert" = "/finance/document/{financial_doc}/revisions/{financial_doc_revision}/revert",
 *     "revision_delete" = "/finance/document/{financial_doc}/revisions/{financial_doc_revision}/delete",
 *     "translation_revert" = "/finance/document/{financial_doc}/revisions/{financial_doc_revision}/revert/{langcode}",
 *     "collection" = "/finance/document",
 *     "collection-for-organisation" = "/finance/{finance_organisation}/document",
 *   },
 *   bundle_entity_type = "financial_doc_type",
 *   field_ui_base_route = "entity.financial_doc_type.edit_form"
 * )
 */
class FinancialDoc extends RevisionableContentEntityBase implements FinancialDocInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);

    if ($org = \Drupal::routeMatch()->getRawParameter('finance_organisation')) {
      $values['organisation'] = $org;
    }
  }

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
  public function getOrganisation() {
    return $this->get('organisation')->entity;
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
    $fields = parent::baseFieldDefinitions($entity_type) + self::ownerBaseFieldDefinitions($entity_type);

    /** @var \Drupal\Core\Field\BaseFieldDefinition $owner */
    $owner = $fields[$entity_type->getKey('owner')];
    $owner->setLabel(t('Authored by'))->setDescription(t('The author of this Financial Document.'));

    $fields['organisation'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Organisation'))
      ->setDescription(t('The Organisation this Financial Document belongs to.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'finance_organisation')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference',
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
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Financial Document entity.'))
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

    return $fields;
  }

}
