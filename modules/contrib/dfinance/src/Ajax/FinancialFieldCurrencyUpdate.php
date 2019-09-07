<?php

namespace Drupal\dfinance\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class FinancialFieldCurrencyUpdateAjaxCommand.
 *
 * Ajax Command used to update the Currency Sign in the field prefix of Financial Fields when
 * associated Currency Entity Reference Fields are changed.
 *
 * @see \Drupal\dfinance\Plugin\Field\FieldWidget\FinancialWidget::ajaxUpdateCurrencySymbol
 */
class FinancialFieldCurrencyUpdate implements CommandInterface {

  private $currency_field;

  private $currency_sign;

  /**
   * FinancialFieldCurrencyUpdate constructor.
   *
   * @param $currency_field
   *   The Field Name of the Currency Entity Reference Field, any Financial Fields using this field
   *   will be updated.
   * @param $currency_sign
   *   The currency sign to set.
   */
  public function __construct($currency_field, $currency_sign) {
    $this->currency_field = $currency_field;
    $this->currency_sign = $currency_sign;
  }

  /**
   * {@inheritDoc}
   */
  public function render() {
    return [
      'command' => 'dfinanceFinancialFieldAjaxUpdateCurrencySymbol',
      'currency_field' => $this->currency_field,
      'currency_sign' => $this->currency_sign,
    ];
  }

}
