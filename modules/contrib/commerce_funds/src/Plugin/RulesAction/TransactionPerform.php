<?php

namespace Drupal\commerce_funds\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\commerce_funds\Entity\Transaction;

/**
 * Perform the transaction.
 *
 * @RulesAction(
 *   id = "commerce_funds_perform_transaction",
 *   label = @Translation("Perform transaction"),
 *   category = @Translation("Transaction"),
 *   context = {
 *     "transaction" = @ContextDefinition("entity:commerce_funds_transaction",
 *       label = @Translation("Transaction"),
 *       description = @Translation("Specifies the transaction that should be performed.")
 *     )
 *   }
 * )
 */
class TransactionPerform extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions(array $selected_data) {
    if ($selected_data && isset($selected_data['transaction'])) {
      $type = $selected_data['transaction']->getDataType();
      $this->getPluginDefinition()['context']['transaction']->setDataType($type);
    }
  }

  /**
   * Perform transaction.
   *
   * @param \Drupal\commerce_funds\Entity\Transaction $transaction
   *   The transaction to be performed.
   */
  protected function doExecute(Transaction $transaction) {
    \Drupal::service('commerce_funds.transaction_manager')->performTransaction($transaction);
  }

}
