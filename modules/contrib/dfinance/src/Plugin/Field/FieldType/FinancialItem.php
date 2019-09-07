<?php

namespace Drupal\dfinance\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\DecimalItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\currency\Entity\CurrencyInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;
use Drupal\dfinance\Entity\OrganisationInterface;
use Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\CurrencyConversionException;

/**
 * Defines the 'financial' field type.
 *
 * @FieldType(
 *   id = "financial",
 *   label = @Translation("Financial"),
 *   description = @Translation("This field stores decimal numbers along with currency and other financial informatin."),
 *   category = @Translation("Number"),
 *   default_widget = "financial",
 *   default_formatter = "financial"
 * )
 */
class FinancialItem extends DecimalItem {

  use CurrencyFieldItemTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      // Prefix and suffix should never be used, but exist because Drupal assumes they exist
      // for example in NumberWidget::formElement.
      'prefix' => '',
      'suffix' => '',
      'min' => '',
      'max' => '',
      'currency' => [
        'date_field' => '',
      ] + CurrencyFieldItemTrait::defaultFieldSettings()
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'value' => [
        'precision' => 16,
        'scale' => 6,
      ],
      'exchange_rate' => [
        'precision' => 16,
        'scale' => 6,
      ],
      'converted_value' => [
        'precision' => 26,
        'scale' => 6,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    /** @var \Drupal\Core\TypedData\DataDefinition[] $properties */

    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Value before conversion'))
      ->setRequired(TRUE);

    $properties += CurrencyFieldItemTrait::propertyDefinitions($field_definition);
    $properties['currency']->setLabel(t('Currency before conversion'));

    $properties['converted_value'] = DataDefinition::create('string')
      ->setLabel(t('Value after conversion'));

    $properties['converted_currency'] = DataDefinition::create('string')
      ->setLabel(t('Currency after conversion'));

    $properties['exchange_rate'] = DataDefinition::create('string')
      ->setLabel(t('Exchange rate that was used'));

    $properties['conversion_date'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Date used to perform the conversion'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'precision' => $field_definition->getSetting('value')['precision'],
          'scale' => $field_definition->getSetting('value')['scale'],
          'not null' => TRUE,
        ],
      ] + CurrencyFieldItemTrait::schemaColumns() + [
        'converted_value' => [
          'type' => 'numeric',
          'precision' => $field_definition->getSetting('converted_value')['precision'],
          'scale' => $field_definition->getSetting('converted_value')['scale'],
          'not null' => TRUE,
        ],
        'converted_currency' => [
          'type' => 'varchar',
          'length' => 5,
          'not null' => TRUE,
        ],
        'exchange_rate' => [
          'type' => 'numeric',
          'precision' => $field_definition->getSetting('exchange_rate')['precision'],
          'scale' => $field_definition->getSetting('exchange_rate')['scale'],
          'not null' => TRUE,
        ],
        'conversion_date' => [
          'type' => 'varchar',
          'length' => 20,
          'not null' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];
    $settings = $this->getSettings();

    $element['info'] = [
      '#markup' => $this->t('These settings only relate to the values stored in the database, the value outputted when the Field or Entity is viewed is formatted based on the currency and locals which are used.  This means that even though the decimal places used for displaying currencies are usually only 2, you can use larger scale values here, such as 4 or 6.  Using these larger scales (or decimal places) is generally advised because it means that for more precise amounts (such as the result of a currency conversion) you do not loose the extra decimal places in the raw value and this may aid financial auditing.')
    ];

    $element['value'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Value'),
      '#tree' => TRUE,
    ];

    $element['value']['info'] = [
      '#markup' => $this->t('This is the raw value which is entered on the form.')
    ];

    $element['value']['precision'] = [
      '#type' => 'number',
      '#title' => t('Precision'),
      '#min' => 10,
      '#max' => 32,
      '#default_value' => $settings['value']['precision'],
      '#description' => t('The total number of digits to store in the database, including those to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    $element['value']['scale'] = [
      '#type' => 'number',
      '#title' => t('Scale', [], ['context' => 'decimal places']),
      '#min' => 0,
      '#max' => 10,
      '#default_value' => $settings['value']['scale'],
      '#description' => t('The number of digits to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    $element['exchange_rate'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Exchange rate'),
      '#tree' => TRUE,
    ];

    $element['exchange_rate']['info'] = [
      '#markup' => $this->t('This is the exchange rate which was used during currency conversion.')
    ];

    $element['exchange_rate']['precision'] = [
      '#type' => 'number',
      '#title' => t('Precision'),
      '#min' => 10,
      '#max' => 32,
      '#default_value' => $settings['exchange_rate']['precision'],
      '#description' => t('The total number of digits to store in the database, including those to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    $element['exchange_rate']['scale'] = [
      '#type' => 'number',
      '#title' => t('Scale', [], ['context' => 'decimal places']),
      '#min' => 0,
      '#max' => 10,
      '#default_value' => $settings['exchange_rate']['scale'],
      '#description' => t('The number of digits to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    $element['converted_value'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Converted Value'),
      '#tree' => TRUE,
    ];

    $element['converted_value']['info'] = [
      '#markup' => $this->t('This is the value once currency conversion has been performed, since this is the result of the Raw Value converted to a different currency the conversion may result in larger numbers so generally the Precision (and possibly Scale) here should be larger than the corresponding values set for the Raw Value above')
    ];

    $element['converted_value']['precision'] = [
      '#type' => 'number',
      '#title' => t('Precision'),
      '#min' => 10,
      '#max' => 32,
      '#default_value' => $settings['converted_value']['precision'],
      '#description' => t('The total number of digits to store in the database, including those to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    $element['converted_value']['scale'] = [
      '#type' => 'number',
      '#title' => t('Scale', [], ['context' => 'decimal places']),
      '#min' => 0,
      '#max' => 10,
      '#default_value' => $settings['converted_value']['scale'],
      '#description' => t('The number of digits to the right of the decimal.'),
      '#disabled' => $has_data,
    ];

    return $element;
  }

  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['min'] = [
      '#type' => 'number',
      '#title' => t('Minimum'),
      '#default_value' => $settings['min'],
      '#step' => pow(0.1, $settings['value']['scale']),
      '#description' => t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
    ];

    $element['max'] = [
      '#type' => 'number',
      '#title' => t('Maximum'),
      '#default_value' => $settings['max'],
      '#step' => pow(0.1, $settings['value']['scale']),
      '#description' => t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
    ];

    $currency_settings = isset($this->getSettings()['currency']) ? $this->getSettings()['currency'] : [];
    $element += $this->currencyFieldTraitFieldSettingsForm($this, $currency_settings);

    $element['currency']['info'] = [
      '#markup' => $this->t('
            <p>Financial Fields can automatically perform currency conversion, the conversion is performed by Drupal when the Entity is saved and using three factors:</p>
            <ul>
                <li>The Currency Entity Reference field that is specified below</li>
                <li>The Currency of the Organisation that is set in the Organisation Entity Reference field specified below</li>
                <li>The Date field that is specified below</li>
            </ul>
            <p>Any resulting currency conversion will, in reality, likely look like: ENTITY CURRENCY converted to ORGANISATION CURRENCY on DATE, with each of these values stored in the field storage for fast accessing.</p>
            <p>Storage of all of these values means that when the Entity is being edited the currency and value prior to conversion will be shown, and when the Entity is being viewed the currency and value after conversion will be shown.</p>
        '),
      '#weight' => -10,
    ];

    $date_field_options = [];
    foreach ($this->getEntityFieldDefinitions() as $field) {
      if ($field->getType() == 'datetime') {
        $date_field_options[$field->getName()] = $field->getName();
      }
    }

    $element['currency']['currency_reference_field']['#required'] = TRUE;

    $element['currency']['organisation_reference_field']['#description'] = $this->t('Defines which Organisation Entity Reference field will be used to get the currency of the Organisation, if no field is specified here then no actual conversion will be performed.');

    $element['currency']['date_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Date field'),
      '#empty_value' => '',
      '#options' => $date_field_options,
      '#default_value' => isset($currency_settings['date_field']) ? $currency_settings['date_field'] : NULL,
      '#description' => $this->t('Defines which Date field will be used for the conversion date, if no field is specified here then no actual conversion will be performed.'),
    ];

    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public function preSave() {
    parent::preSave();

    // Set the converted values to the raw values first
    $this->set('converted_value', $this->value);

    $settings = $this->getSetting('currency');
    $currency_entity = $this->getReferencedEntityUsingSettings($settings, 'currency_reference_field');
    if (!$currency_entity instanceof CurrencyInterface) {
      return;
    }
    CurrencyFieldItemTrait::currencyFieldTraitPreSave($this, $currency_entity);
    $this->set('converted_currency', $currency_entity->id());
    $this->set('exchange_rate', 1);
    $conversion_date = gmdate('Y-m-d', \Drupal::time()->getRequestTime());
    $this->set('conversion_date', $conversion_date);

    // Now attempt to get the organisation and perform actual conversion
    $organisation_entity = $this->getReferencedEntityUsingSettings($settings, 'organisation_reference_field');
    if (!$organisation_entity instanceof OrganisationInterface) {
      return;
    }
    $convert_currency_entity = $organisation_entity->getCurrency();
    if (!$convert_currency_entity instanceof CurrencyInterface) {
      return;
    }

    $date_field_name = $this->getSetting('currency')['date_field'];
    if ($this->getEntity()->hasField($date_field_name)) {
      $date_field = $this->getEntity()->get($date_field_name);
      if ($date_field instanceof DateTimeFieldItemList) {
        // @todo: do we need some more robust date formatting here?
        $conversion_date = $date_field->value;
        $this->set('conversion_date', $conversion_date);
      }
    }

    /** @var \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\ExchangeRatesAPIdotIO $currency_exchange */
    $currency_exchange = \Drupal::service('plugin.manager.currency.exchange_rate_provider')->createInstance('exchange_rates_api_dot_io');

    try {
      $conversion_result = $currency_exchange->loadByDate($currency_entity->id(), $convert_currency_entity->id(), new \DateTime($conversion_date));
    } catch (CurrencyConversionException $ex) {
      \Drupal::messenger()->addError($this->t('Currency conversion failed for field %field_id, please contact the site administrator, the field will be saved with the original value and currency entered.  Error details: %error_message', [
        '%field_id' => $this->getFieldDefinition()->getName(),
        '%error_message' => $ex->getMessage(),
      ]));
      return;
    }

    $conversion = bcmul($this->value, $conversion_result->getRate(), 6);

    $this->set('converted_currency', $convert_currency_entity->id());
    $this->set('converted_value', $conversion);
    $this->set('exchange_rate', $conversion_result->getRate());
  }

}