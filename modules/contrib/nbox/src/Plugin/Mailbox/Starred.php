<?php

namespace Drupal\nbox\Plugin\Mailbox;

use Drupal\nbox\Plugin\MailboxBase;
use Drupal\nbox\Plugin\MailboxRule;

/**
 * Provides an starred Mailbox.
 *
 * @Mailbox(
 *  id = "starred",
 *  label = @Translation("Starred"),
 *  weight = 8,
 *  showUnread = TRUE,
 * )
 */
class Starred extends MailboxBase {

  /**
   * {@inheritdoc}
   */
  public function setViewsFilterQuery() {
    $this->rules[] = new MailboxRule('starred', TRUE, '=');
    $this->rules[] = new MailboxRule('deleted', 'not_deleted', '=');
  }

}
