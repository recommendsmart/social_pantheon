<?php

namespace Drupal\dfinance\Plugin\Currency\ExchangeRateProvider;

use BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;
use Drupal\Core\Plugin\PluginBase;
use Drupal\currency\ExchangeRate;
use Drupal\currency\Plugin\Currency\ExchangeRateProvider\ExchangeRateProviderInterface;

/**
 * Provides historical exchange rates.
 *
 * @CurrencyExchangeRateProvider(
 *   id = "exchange_rates_api_dot_io",
 *   label = @Translation("ExchangeRatesAPI.io")
 * )
 */
class ExchangeRatesAPIdotIO extends PluginBase implements ExchangeRateProviderInterface {

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\CurrencyConversionException
   *   Thrown if there was a problem with data supplied or it was not possible to perform an exchange
   */
  public function load($sourceCurrencyCode, $destinationCurrencyCode) {
    return $this->loadByDate($sourceCurrencyCode, $destinationCurrencyCode, NULL);
  }

  /**
   * @see \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\ExchangeRatesAPIdotIO::load()
   *
   * @param \DateTime $date|NULL
   *   Date to use to lookup exchange rate, or NULL to use the latest rate.
   *
   * @return \Drupal\currency\ExchangeRateInterface|NULL
   *
   * @throws \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\CurrencyConversionException
   *   Thrown if there was a problem with data supplied or it was not possible to perform an exchange
   */
  public function loadByDate($sourceCurrencyCode, $destinationCurrencyCode, \DateTime $date = NULL) {
    $return = $this->loadMultipleByDate([
      $sourceCurrencyCode => [ $destinationCurrencyCode ]
    ], $date);
    return isset($return[$sourceCurrencyCode][$destinationCurrencyCode]) ? $return[$sourceCurrencyCode][$destinationCurrencyCode] : NULL;
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\CurrencyConversionException
   *   Thrown if there was a problem with data supplied or it was not possible to perform an exchange
   */
  public function loadMultiple(array $currencyCodes) {
    return $this->loadMultipleByDate($currencyCodes, 'latest');
  }

  /**
   * @see \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\ExchangeRatesAPIdotIO::loadMultiple()
   *
   * @param string $date
   *   Date to use to lookup exchange rate.
   *
   * @return array
   *
   * @throws \Drupal\dfinance\Plugin\Currency\ExchangeRateProvider\CurrencyConversionException
   *   Thrown if there was a problem with data supplied or it was not possible to perform an exchange
   */
  public function loadMultipleByDate(array $currencyCodes, \DateTime $date = NULL) {
    $date_formatted = 'latest';

    if ($date != NULL) {
      $date_formatted = $date->format('Y-m-d');
      $now = new \DateTime('now');

      if ($date > $now) {
        throw new CurrencyConversionException("Attempted to preform a currency conversion but the date $date_formatted is in the future.");
      }
    }

    /** @var \GuzzleHttp\ClientInterface $client */
    $client = \Drupal::httpClient();
    $return = [];

    foreach ($currencyCodes as $sourceCode => $destCodes) {

      try {
        $request = $client->request('GET', "https://api.exchangeratesapi.io/$date_formatted", [
          'query' => [
            'base' => $sourceCode,
            'symbols' => implode(',', $destCodes),
          ],
        ]);
      } catch (\RuntimeException $ex) {
        throw new CurrencyConversionException("While trying to get currency conversion rates an error occurred: {$ex->getMessage()}");
      }

      $response = json_decode($request->getBody());

      if ($response == NULL) {
        throw new CurrencyConversionException("Unable to parse the response from the currency conversion service as JSON, the HTTP status code returned was {$request->getStatusCode()}, response body: {$request->getBody()}");
      }

      if (isset($response->error)) {
        throw new CurrencyConversionException("While trying to get currency conversion rates the currency conversion service returned an error message: {$response->error}");
      }

      foreach ($response->rates as $ex_code => $rate) {
        $return[$response->base] = [ $ex_code => new ExchangeRate($response->base, $ex_code, $rate, $this->getPluginId()) ];
      }
    }

    return $return;
  }

}
