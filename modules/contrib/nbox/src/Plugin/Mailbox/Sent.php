<?php

namespace Drupal\nbox\Plugin\Mailbox;

use Drupal\nbox\Plugin\MailboxBase;
use Drupal\nbox\Plugin\MailboxRule;

/**
 * Provides an sent Mailbox.
 *
 * @Mailbox(
 *  id = "sent",
 *  label = @Translation("Sent"),
 *  weight = 5,
 *  showUnread = FALSE,
 * )
 */
class Sent extends MailboxBase {

  /**
   * {@inheritdoc}
   */
  public function setViewsFilterQuery() {
    $this->rules[] = new MailboxRule('deleted', 'not_deleted', '=');
    $this->rules[] = new MailboxRule('sender', TRUE, '=');
    $this->rules[] = new MailboxRule('draft', FALSE, '=');
  }

}
