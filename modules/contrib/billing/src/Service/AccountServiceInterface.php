<?php

namespace Drupal\billing\Service;

/**
 * Interface \Drupal\billing\Service\AccountServiceInterface.
 */
interface AccountServiceInterface {

  /**
   * Get default account.
   * @param  string         $currency
   * @param  string         $type
   * @return BillingAccount $account
   */
  public function getDefaultAccount(string $currency, string $type);

  /**
   * Recalc.
   * @param  BillingAccount $account
   * @return float          $amount
   */
  public function reCalc($account);

  /**
   * Get user account.
   * @param  int            $uid
   * @param  string         $currency
   * @return BillingAccount $account
   */
  public function getUserAccount(int $uid, $currency = 'XXX');

  /**
   * Render account balance.
   * @param  User   $user
   * @return string $render
   */
  public function renderAccountBalance($user);

}
