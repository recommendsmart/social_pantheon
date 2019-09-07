<?php

namespace Drupal\dfinance\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\dfinance\Entity\OrganisationInterface;
use MathParser\Interpreting\Evaluator;
use MathParser\StdMathParser;

/**
 * Defines the 'formula' field type.
 *
 * @FieldType(
 *   id = "formula",
 *   label = @Translation("Formula (Experimental)"),
 *   description = @Translation("Produces an output based on a mathematical forumla and values of other fields."),
 *   category = @Translation("Number"),
 *   default_formatter = "number_decimal"
 * )
 */
class FormulaItem extends FieldItemBase {

  use CurrencyFieldItemTrait;

  /**
   * {@inheritDoc{
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    /** @var \Drupal\Core\TypedData\DataDefinition[] $properties */

    $properties['formula'] = DataDefinition::create('string')
      ->setLabel(t('The formula used.'))
      ->setRequired(TRUE);

    $properties += CurrencyFieldItemTrait::propertyDefinitions($field_definition);

    $properties['computed_value'] = DataDefinition::create('string')
      ->setLabel(t('The value computed from the formula.'));

    return $properties;
  }

  public static function mainPropertyName() {
    return 'computed_value';
  }

  /**
   * {@inheritDoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'formula' => [
          'type' => 'text',
          'not null' => TRUE,
        ],
        'computed_value' => [
          'type' => 'text',
          'not null' => TRUE,
        ],
      ] + CurrencyFieldItemTrait::schemaColumns()
    ];
  }

  /**
   * {@inheritDoc}
   */
  public static function defaultFieldSettings() {
    return [
      'formula' => '',
      'currency' => [
        'currency_from' => 'none',
      ] + CurrencyFieldItemTrait::defaultFieldSettings()
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();

    $element['formula'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Formula'),
      '#description' => $this->t('The formula to use'),
      '#rows' => 3,
      '#default_value' => $settings['formula'],
      '#required' => TRUE,
    ];
    $element['formula_token_tree'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [$this->getEntity()->getEntityTypeId()],
    ];

    $currency_settings = isset($this->getSettings()['currency']) ? $this->getSettings()['currency'] : [];
    $element += $this->currencyFieldTraitFieldSettingsForm($this, $currency_settings);

    $element['currency']['info'] = [
      '#markup' => $this->t('
            <p>Currency Options are only used when the Financial Field Formatter is used.</p>
            <p>If you want this Formula Field to be displayed and formatted using a Currency, you can specify which fields on the Entity will be used to provide the Currency which will be used.</p>
            <p>Unlike the Financial Field Type, Formula Fields do not perform any conversion, Currency Options here are simply used for formatting output.</p>
        '),
      '#weight' => -10,
    ];

    $element['currency']['currency_from'] = [
      '#type' => 'radios',
      '#title' => $this->t('Get currency from:'),
      '#default_value' => isset($currency_settings['currency_from']) ? $currency_settings['currency_from'] : 'none',
      '#options' => [
        'none' => $this->t('Nothing (no currency information will be provided when formatting this field)'),
        'currency_reference_field' => $this->t('Currency Entity Reference field'),
        'organisation_reference_field' => $this->t('Organisation Entity Reference field'),
      ],
      '#required' => TRUE,
      '#weight' => -4,
    ];

    $element['currency']['currency_reference_field']['#states'] = [
      'visible' => [
        ':input[name="settings[currency][currency_from]"]' => [ 'value' => 'currency_reference_field' ],
      ],
    ];

    $element['currency']['organisation_reference_field']['#states'] = [
      'visible' => [
        ':input[name="settings[currency][currency_from]"]' => [ 'value' => 'organisation_reference_field' ],
      ],
    ];

    return $element;
  }

  public function preSave() {
    parent::preSave();

    $settings = $this->getSetting('currency');

    $currency_from = isset($settings['currency_from']) ? $settings['currency_from'] : 'none';
    if ($currency_from == 'none') {
      CurrencyFieldItemTrait::currencyFieldTraitPreSave($this, NULL);
    }
    elseif ($currency_from == 'currency_reference_field') {
      $currency_entity = $this->getReferencedEntityUsingSettings($settings, 'currency_reference_field');
      if ($currency_entity instanceof CurrencyInterface) {
        CurrencyFieldItemTrait::currencyFieldTraitPreSave($this, $currency_entity);
      }
    }
    elseif ($currency_from == 'organisation_reference_field') {
      $organisation_entity = $this->getReferencedEntityUsingSettings($settings, 'organisation_reference_field');
      if ($organisation_entity instanceof OrganisationInterface) {
        CurrencyFieldItemTrait::currencyFieldTraitPreSave($this, $organisation_entity->getCurrency());
      }
    }

    $formula = $this->getSetting('formula');

    $entity = $this->getEntity();
    $formula = \Drupal::token()->replace($formula, [$entity->getEntityTypeId() => $entity]);

    $parser = new StdMathParser();
    $AST = $parser->parse($formula);
    $evaluator = new Evaluator();
    $value = $AST->accept($evaluator);

    $this->set('computed_value', $value);
  }

}