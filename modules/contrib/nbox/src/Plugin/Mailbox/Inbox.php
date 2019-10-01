<?php

namespace Drupal\nbox\Plugin\Mailbox;

use Drupal\nbox\Plugin\MailboxBase;
use Drupal\nbox\Plugin\MailboxRule;

/**
 * Provides an inbox Mailbox.
 *
 * @Mailbox(
 *  id = "inbox",
 *  label = @Translation("Inbox"),
 *  weight = 0,
 *  showUnread = TRUE,
 * )
 */
class Inbox extends MailboxBase {

  /**
   * {@inheritdoc}
   */
  public function setViewsFilterQuery() {
    $this->rules[] = new MailboxRule('deleted', 'not_deleted', '=');
    $this->rules[] = new MailboxRule('recipient', TRUE, '=');
    $this->rules[] = new MailboxRule('draft', FALSE, '=');
  }

}
