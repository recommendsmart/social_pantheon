<?php

namespace Drupal\billing\Service;

/**
 * Class AccountService.
 */
class AccountService implements AccountServiceInterface {

  /**
   * Constructs a new AccountService object.
   */
  public function __construct() {
    $this->id = 0;
    $this->type = 'system';
    $this->currency = 'XXX';
    $this->currencyService = \Drupal::service('billing.currency');
    $this->accountStorage = \Drupal::entityTypeManager()->getStorage('billing_account');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultAccount(string $currency = 'XXX', string $type = 'system') {
    $this->type = $type;
    $this->currency = $currency;
    $account = $this->query();
    if (!$account) {
      $account = $this->createAccount();
    }
    return $account;
  }

  /**
   * {@inheritdoc}
   */
  public function reCalc($account) {
    $amount = 0;
    $this->id = $account->id();
    $this->currency = $account->currency->target_id;
    if ($transactions = $this->reCalcQuery()) {
      foreach ($transactions as $key => $value) {
        $amount += bcadd((float) $value->debit, -(float) $value->credit, 2);
      }
    }
    $account->amount->setValue($amount);
    $account->save();
    return $amount;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserAccount(int $uid, $currency = 'XXX') {
    $account = FALSE;
    $this->id = $uid;
    $this->currency = $currency;
    if ($this->id) {
      $account = $this->getDefaultAccount($this->currency, 'user');
    }
    return $account;
  }

  /**
   * {@inheritdoc}
   */
  public function renderAccountBalance($user) {
    $name = $user->name->value;
    $render = "<h4>Баланс $name:</h4>";
    foreach ($this->currencyService->formOptions() as $key => $option) {
      $currencyCode = $option->getArguments()['@currency_code'];
      $billingAccount = $this->getUserAccount($user->id(), $key);
      $amount = (float) $billingAccount->get('amount')->value;
      $amountHuman = number_format($amount, 3, '.', ' ');
      $render .= "<b>$amountHuman</b> $currencyCode.<br />";
    }
    return $render;
  }

  /**
   * Query.
   * @return BillingAccount $account
   */
  private function query() {
    $account = FALSE;
    $query = \Drupal::entityQuery('billing_account')
      ->condition('status', 1)
      ->sort('created', 'ASC')
      ->condition('entity_type', $this->type)
      ->condition('entity_id', $this->id)
      ->condition('currency', $this->currency)
      ->range(0, 1);

    $ids = $query->execute();
    if (!empty($ids)) {
      $account = $this->accountStorage->load(array_shift($ids));
    }
    return $account;
  }

  /**
   * Create account.
   * @return BillingAccount $account
   */
  private function createAccount() {
    $account = $this->accountStorage->create([
      'name' => "$this->type - $this->id ($this->currency)",
      'entity_type' => $this->type,
      'entity_id' => $this->id,
      'currency' => $this->currency,
    ]);
    $account->save();
    return $account;
  }

  /**
   * Recalc query.
   * @return array BillingTransactions
   */
  private function reCalcQuery() {
    $fields = [
      'entity_id',
      'debit',
      'credit',
      'currency',
    ];

    return \Drupal::database()->select('billing_transaction', 'transactions')
      ->fields('transactions', $fields)
      ->condition('status', 1)
      ->condition('currency', $this->currency)
      ->condition('account_id', $this->id)
      ->execute();
  }

}
