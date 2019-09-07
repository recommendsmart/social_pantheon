<?php

namespace Drupal\dfinance\Plugin\Field\FieldFormatter;

/**
 * Implementation of the 'financial' formatter specifically for 'formula' fields.
 *
 * @FieldFormatter(
 *   id = "formula_financial",
 *   label = @Translation("Financial"),
 *   field_types = {
 *     "formula"
 *   }
 * )
 */
class FormulaFinancialFormatter extends FinancialFormatter {

  /**
   * {@inheritDoc}
   */
  public function valuePropertyName() {
    return 'computed_value';
  }

  /**
   * {@inheritDoc}
   */
  public function currencyPropertyName() {
    return 'currency';
  }


}
