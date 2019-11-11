<?php

namespace Drupal\billing\Service;

/**
 * Class CurrencyService.
 */
class CurrencyService implements CurrencyServiceInterface {


  /**
   * Constructs a new CurrencyService object.
   */
  public function __construct() {
    $this->currencyService = \Drupal::service('currency.form_helper');
  }

  /**
   * {@inheritdoc}
   */
  public function checkCurrency($currency) {
    $options = $this->formOptions();
    return isset($options[$currency]) ? $currency : 'XXX';
  }

  /**
   * {@inheritdoc}
   */
  public function formOptions() {
    return $this->currencyService->getCurrencyOptions();
  }

}
