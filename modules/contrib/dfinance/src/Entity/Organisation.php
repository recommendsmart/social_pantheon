<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\currency\Entity\CurrencyInterface;

/**
 * Defines the Organisation entity.
 *
 * @ingroup dfinance
 *
 * @ContentEntityType(
 *   id = "finance_organisation",
 *   label = @Translation("Organisation"),
 *   label_collection = @Translation("Manage Organisations"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dfinance\Controller\OrganisationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\dfinance\Form\OrganisationForm",
 *       "add" = "Drupal\dfinance\Form\OrganisationForm",
 *       "edit" = "Drupal\dfinance\Form\OrganisationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\dfinance\Access\OrganisationAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "finance_organisation",
 *   admin_permission = "administer finance organisation entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/finance/{finance_organisation}",
 *     "add-form" = "/admin/finance/organisation/add",
 *     "edit-form" = "/finance/{finance_organisation}/settings",
 *     "delete-form" = "/finance/{finance_organisation}/delete",
 *     "collection" = "/admin/finance/organisation",
 *   },
 *   field_ui_base_route = "entity.finance_organisation.collection"
 * )
 */
class Organisation extends ContentEntityBase implements OrganisationInterface {

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
   * {@inheritDoc}
   */
  public function getCurrency() {
    $field = $this->get('currency');
    if ($field instanceof EntityReferenceFieldItemListInterface) {
      return $field->referencedEntities()[0];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getCurrencyId() {
    return $this->get('currency')->target_id;
  }

  /**
   * {@inheritDoc}
   */
  public function setCurrency(CurrencyInterface $currency) {
    $this->set('currency', $currency->id());
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function setCurrencyId($currency) {
    $this->set('currency', $currency);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Organisation entity.'))
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

    // @todo identify if it's safe to allow the currency to be changed
    $fields['currency'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Organisation currency'))
      ->setDescription('The currency used by this organisation, this will be used by any Financial fields to convert from a foreign currency.')
      ->setSettings([
        'target_type' => 'currency',
        'handler' => 'default',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference',
        'label' => 'above',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}
