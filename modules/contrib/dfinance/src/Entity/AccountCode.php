<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Account Code entity.
 *
 * @ingroup dfinance
 *
 * @ContentEntityType(
 *   id = "financial_account_code",
 *   label = @Translation("Account Code"),
 *   label_collection = @Translation("Account Codes"),
 *   bundle_label = @Translation("Account Code type"),
 *   handlers = {
 *     "storage" = "Drupal\dfinance\Entity\Storage\AccountCodeStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dfinance\Controller\AccountCodeListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\dfinance\Form\AccountCodeForm",
 *       "add" = "Drupal\dfinance\Form\AccountCodeForm",
 *       "edit" = "Drupal\dfinance\Form\AccountCodeForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\dfinance\Routing\AccountCodeHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\dfinance\Access\AccountCodeAccessControlHandler",
 *   },
 *   base_table = "financial_account_code",
 *   revision_table = "financial_account_code_revision",
 *   revision_data_table = "financial_account_code_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = FALSE,
 *   admin_permission = "administer account code entities",
 *   entity_keys = {
 *     "id" = "code",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "author_user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/finance/financial_account_code/{financial_account_code}",
 *     "add-page" = "/admin/finance/financial_account_code/add",
 *     "add-form" = "/admin/finance/financial_account_code/add/{financial_account_code_type}",
 *     "edit-form" = "/admin/finance/financial_account_code/{financial_account_code}/edit",
 *     "delete-form" = "/admin/finance/financial_account_code/{financial_account_code}/delete",
 *     "version-history" = "/admin/finance/financial_account_code/{financial_account_code}/revisions",
 *     "revision" = "/admin/finance/financial_account_code/{financial_account_code}/revisions/{financial_account_code_revision}/view",
 *     "revision_revert" = "/admin/finance/financial_account_code/{financial_account_code}/revisions/{financial_account_code_revision}/revert",
 *     "revision_delete" = "/admin/finance/financial_account_code/{financial_account_code}/revisions/{financial_account_code_revision}/delete",
 *     "collection" = "/admin/finance/financial_account_code",
 *   },
 *   bundle_entity_type = "financial_account_code_type",
 *   field_ui_base_route = "entity.financial_account_code_type.edit_form"
 * )
 */
class AccountCode extends ContentEntityBase implements AccountCodeInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use RevisionLogEntityTrait;

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
   * {@inheritDoc}
   */
  public function label() {
    $label = parent::label();
    return is_null($label) ? $this->id() : "{$this->id()} $label";
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the revision metadata fields.
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    // Add the owner base fields.
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    /** @var \Drupal\Core\Field\BaseFieldDefinition $owner */
    $owner = $fields[$entity_type->getKey('owner')];
    $owner->setLabel(t('Authored by'))->setDescription(t('The user who created this Account Code.'));

    $chart_of_accounts = Link::fromTextAndUrl('Chart of Accounts', Url::fromUri('https://en.wikipedia.org/wiki/Chart_of_accounts'))->toString();

    $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('string')
      ->setLabel(t('Account Code ID'))
      ->setDescription(t('This is typically a 3 or 4 digit code defined in the Organisation\'s %chart_of_accounts, this must be unique and cannot be changed once it is set.', [
        '%chart_of_accounts' => $chart_of_accounts,
      ]))
      ->setSettings([
        'max_length' => 20,
        'text_processing' => 0,
      ])
      ->addConstraint('dfinance_account_code')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields[$entity_type->getKey('label')] = BaseFieldDefinition::create('string')
      ->setLabel(t('Account code name'))
      ->setDescription(t('The name of this Account Code, this is usually a short description, as with the Account Code itself you should consult your organisation\'s %chart_of_accounts.', [
        '%chart_of_accounts' => $chart_of_accounts,
      ]))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
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
