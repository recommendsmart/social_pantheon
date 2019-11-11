<?php

namespace Drupal\billing\Service;

/**
 * Interface \Drupal\billing\Service\CurrencyServiceInterface.
 */
interface CurrencyServiceInterface {

  /**
   * Check currency.
   * @param  string $currency
   * @return string
   */
  public function checkCurrency($currency);

  /**
   * Duble: \Drupal::service('currency.form_helper')->getCurrencyOptions()
   * @return array
   */
  public function formOptions();

}
