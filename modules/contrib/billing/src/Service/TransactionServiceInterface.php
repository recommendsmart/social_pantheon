<?php

namespace Drupal\billing\Service;

/**
 * Interface \Drupal\billing\Service\TransactionServiceInterface.
 */
interface TransactionServiceInterface {

  /**
   * Make deal.
   * @param  array  $transaction
   * @param  string $comment
   */
  public function deal(array $transaction, string $comment);

}
