<?php

namespace Drupal\dfinance\Plugin\Field\FieldType;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\currency\Entity\CurrencyInterface;

/**
 * A trait which supports currency aware field items.
 *
 * {@internal}
 */
trait CurrencyFieldItemTrait {

  use StringTranslationTrait;

  public static function defaultFieldSettings() {
    return [
      'currency_reference_field' => '',
      'organisation_reference_field' => '',
    ];
  }

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['currency'] = DataDefinition::create('string')
      ->setLabel(t('Currency'));

    return $properties;
  }

  public static function schemaColumns() {
    return [
      'currency' => [
        'type' => 'varchar',
        'length' => 5,
        'not null' => TRUE,
      ],
    ];
  }

  /**
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   */
  public abstract function getEntity();

  protected function getEntityFieldDefinitions() {
    /** @var \Drupal\core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $entityTypeID = $this->getEntity()->getEntityTypeId();
    $bundle = $this->getEntity()->bundle();
    return $entityFieldManager->getFieldDefinitions($entityTypeID, $bundle);
  }

  protected function currencyFieldOptions() {
    $options = [];

    $fields = $this->getEntityFieldDefinitions();

    foreach ($fields as $field) {
      if ($field->getType() == 'entity_reference' && $field->getSetting('target_type') == 'currency') {
        $options[$field->getName()] = $field->getName();
      }
    }

    return $options;
  }

  protected function organisationFieldOptions() {
    $options = [];

    $fields = $this->getEntityFieldDefinitions();

    foreach ($fields as $field) {
      if ($field->getType() == 'entity_reference' && $field->getSetting('target_type') == 'finance_organisation') {
        $options[$field->getName()] = $field->getName();
      }
    }

    return $options;
  }

  public function currencyFieldTraitFieldSettingsForm(FieldItemInterface $field, $settings) {
    $element['currency'] = [
      '#type' => 'fieldset',
      '#title' => 'Currency Options',
      '#tree' => TRUE,
    ];

    $num_exists = \Drupal::entityQuery($field->getEntity()->getEntityTypeId())
      ->exists($field->getFieldDefinition()->getName())
      ->count()
      ->execute();

    if ($num_exists > 0) {
      $element['currency']['warning'] = [
        '#theme' => 'status_messages',
        '#message_list' => [ 'warning' => [
          $this->t('There is data for this field in the database, any changes to the Currency Options section will not automatically affect existing entities, these settings are only used when entities are created or updated.'),
        ]],
        '#status_headings' => [ 'warning' => $this->t('Warning message') ],
        '#weight' => -5,
      ];
    }

    $element['currency']['currency_reference_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency Entity Reference field'),
      '#options' => $this->currencyFieldOptions(),
      '#empty_value' => '',
      '#default_value' => isset($settings['currency_reference_field']) ? $settings['currency_reference_field'] : NULL,
      '#description' => $this->t('Defines which Currency Entity Reference field will be used to set the currency of this field.'),
    ];

    $element['currency']['organisation_reference_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Organisation Entity Reference field'),
      '#options' => $this->organisationFieldOptions(),
      '#empty_value' => '',
      '#default_value' => isset($settings['organisation_reference_field']) ? $settings['organisation_reference_field'] : NULL,
      '#description' => $this->t('Defines which Organisation Entity Reference field will be used to get the currency of the Organisation.'),
    ];

    return $element;
  }

  public static function currencyFieldTraitPreSave(FieldItemInterface $field_item, $currency_entity) {
    $self_class = self::class;
    $currency_class = CurrencyInterface::class;
    if ($currency_entity instanceof CurrencyInterface) {
      $field_item->set('currency', $currency_entity->id());
    }
    elseif ($currency_entity == NULL) {
      $field_item->set('currency', NULL);
    }
    else {
      throw new \TypeError("Argument 2 passed to $self_class::currencyFieldTraitPreSave() must either implement interface $currency_class or pass NULL, passing NULL unsets the currently saved Currency.");
    }
  }

  /**
   * @param array $settings
   * @param string $currency_settings_key
   * @return \Drupal\Core\Entity\EntityInterface|NULL
   */
  public function getReferencedEntityUsingSettings($settings, $currency_settings_key) {
    $entity = $this->getEntity();
    if (!isset($settings[$currency_settings_key]) || !$entity->hasField($settings[$currency_settings_key])) {
      return NULL;
    }
    $fieldList = $entity->get($settings[$currency_settings_key]);
    if (!($fieldList instanceof EntityReferenceFieldItemListInterface)) {
      return NULL;
    }
    $entities = $fieldList->referencedEntities();
    return count($entities) > 0 ? $entities[0] : NULL;
  }


}