<?php

namespace Drupal\dfinance\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\currency\Entity\CurrencyInterface;

/**
 * Plugin implementation of the 'number_decimal' formatter.
 *
 * The 'Default' formatter is different for integer fields on the one hand, and
 * for decimal and float fields on the other hand, in order to be able to use
 * different settings.
 *
 * @FieldFormatter(
 *   id = "financial",
 *   label = @Translation("Financial"),
 *   field_types = {
 *     "financial"
 *   }
 * )
 */
class FinancialFormatter extends FormatterBase {

  /**
   * Returns the name of the property used for the value.
   *
   * @return string
   *   Name of the property.
   */
  public function valuePropertyName() {
    return 'converted_value';
  }

  /**
   * Returns the name of the property which stores the ID of the Currency Entity to use.
   *
   * @return string
   *   Name of the property.
   */
  public function currencyPropertyName() {
    return 'converted_currency';
  }

  /**
   * Gets the Currency Entity which should be used to display the currency.
   *
   * @param FieldItemInterface $item
   *   Field Item.
   *
   * @return \Drupal\currency\Entity\CurrencyInterface|NULL
   *   Currency Entity or NULL if non could be determined.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getCurrencyEntity(FieldItemInterface $item) {
    $currency_id = $item->get($this->currencyPropertyName())->getValue();
    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = \Drupal::entityTypeManager()->getStorage('currency')->load($currency_id);
    return $currency;
  }

  /**
   * {@inheritDoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\dfinance\Plugin\Field\FieldType\FinancialItem $item */
    foreach ($items as $delta => $item) {
      $value = $item->get($this->valuePropertyName())->getValue();
      $currency = $this->getCurrencyEntity($item);

      if ($currency instanceof CurrencyInterface) {
        $output = $currency->formatAmount($value, TRUE);
      } else {
        $output = $value;
      }

      // Output the raw value in a content attribute if the text of the HTML
      // element differs from the raw value (for example when a prefix is used).
      if (isset($item->_attributes) && $value != $output) {
        $item->_attributes += ['content' => $value];
      }

      $elements[$delta] = ['#markup' => $output];
    }

    return $elements;
  }

}