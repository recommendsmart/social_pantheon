<?php

namespace Drupal\billing\Service;

/**
 * Class TransactionService.
 */
class TransactionService implements TransactionServiceInterface {


  /**
   * Constructs a new TransactionService object.
   */
  public function __construct() {
    $this->debit = FALSE;
    $this->credit = FALSE;
    $this->accountService = \Drupal::service('billing.account');
    $this->currencyService = \Drupal::service('billing.currency');
    $this->transactionStorage = \Drupal::entityTypeManager()->getStorage('billing_transaction');
  }

  /**
   * {@inheritdoc}
   */
  public function deal(array $transaction, string $comment) {
    $sum = FALSE;
    if (isset($transaction['sum']) && $transaction['sum'] != 0) {
      $sum = $transaction['sum'];
    }

    if (isset($transaction['debit_account'])) {
      $this->debit = $transaction['debit_account'];
    }
    elseif (isset($transaction['account'])) {
      $this->debit = $transaction['account'];
    }
    else {
      return FALSE;
    }
    $currency = $this->currencyService->checkCurrency($this->debit->currency->target_id);

    if (isset($transaction['credit_account'])) {
      $this->credit = $transaction['credit_account'];
      $credit_currency = $this->credit->currency->target_id;
      if ($credit_currency != $currency) {
        drupal_set_message("Wrong currency : $credit_currency != $currency", 'error');
        return FALSE;
      }
    }
    if (!$this->credit) {
      $this->credit = $this->accountService->getDefaultAccount($currency);
    }

    $entity_type = 'correction';
    $entity_id = 0;
    if (isset($transaction['reason']) && is_object($transaction['reason'])) {
      $reason_entity = $transaction['reason'];
      $entity_type = $reason_entity->bundle();
      $entity_id = $reason_entity->id();
    }

    if ($sum < 0) {
      $sum = -$sum;
      $debit_to_credit = $this->debit;
      $this->debit = $this->credit;
      $this->credit = $debit_to_credit;
    }

    if ($sum > 0 && $this->debit && $this->credit) {
      $transaction_debit = [
        'name' => $comment,
        'created' => REQUEST_TIME,
        'account_id' => $this->debit->id(),
        'debit' => $sum,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'currency' => $currency,
      ];
      $transaction_credit = [
        'name' => $comment,
        'created' => REQUEST_TIME,
        'account_id' => $this->credit->id(),
        'credit' => $sum,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'currency' => $currency,
      ];
      $hash = $this->getHash($transaction_debit, $transaction_credit);
      $transaction_debit['hash'] = $hash;
      $transaction_credit['hash'] = $hash;

      $this->createQueue($transaction_debit, $transaction_credit);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function runQueue($data) {
    $this->createTransaction($data['debit_data']);
    $this->createTransaction($data['credit_data']);
    $this->accountService->reCalc($data['debit']);
    $this->accountService->reCalc($data['credit']);
  }

  /**
   * Hash protection.
   * @param  array  $d
   * @param  array  $c
   * @return string
   */
  private function getHash(array $d, array $c) {
    $history = '';
    $tranactions = [];

    $current = "[credit]:{$d['created']}:{$d['debit']}:{$d['account_id']}:{$d['account_id']}:{$d['currency']}:{$d['entity_id']}";
    $current .= "[debit]:{$c['created']}:{$c['credit']}:{$c['account_id']}:{$c['account_id']}:{$c['currency']}:{$c['entity_id']}";
    $hash_current = hash('sha256', $current);

    $ids = \Drupal::entityQuery('billing_transaction')
      ->condition('status', 1)
      ->sort('id', 'DESC')
      ->range(0, 10)
      ->execute();

    foreach ($this->transactionStorage->loadMultiple($ids) as $tranaction) {
      $id = $tranaction->id();
      $time = $tranaction->created->value;
      $d = $tranaction->debit->value;
      $c = $tranaction->credit->value;
      $aid = $tranaction->account_id->entity->id();
      $etype = $tranaction->entity_type->value;
      $eid = $tranaction->entity_id->value;
      $cur = $tranaction->currency->target_id;
      $h = $tranaction->hash->value;
      $history .= "[{$id}]:{$time}:{$d}:{$c}:{$cur}:{$aid}:{$etype}:{$eid}:{$h}\n";
    }

    $hash_history = hash('sha256', $history);
    return substr($hash_current, 0, 12) . ":" . substr($hash_history, 0, 12);
  }

  /**
   * Create transaction.
   * @param  array              $transaction_array
   * @return BillingTransaction $transaction
   */
  private function createTransaction(array $transaction_array) {
    $transaction = $this->transactionStorage->create($transaction_array);
    $transaction->save();
    return $transaction;
  }

  /**
   * Create transaction.
   * @param  array              $transaction_array
   * @return BillingTransaction $transaction
   */
  private function createQueue($debit, $credit) {
    $queue = \Drupal::queue('billing_transactions');
    $queue->createQueue();
    $queue->createItem([
      'id' => $id,
      'type' => 'billing_transaction',
      'debit_data' => $debit,
      'credit_data' => $credit,
      'debit' => $this->debit,
      'credit' => $this->credit,
    ]);
  }

}
