<?php

namespace Drupal\billing\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Process a queue.
 *
 * @QueueWorker(
 *   id = "billing_transactions",
 *   title = @Translation("Create billing transactions"),
 *   cron = {"time" = 20}
 * )
 */
class Transactions extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    \Drupal::service('billing.transaction')->runQueue($data);
    return $data;
  }

}
