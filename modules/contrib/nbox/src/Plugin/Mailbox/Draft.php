<?php

namespace Drupal\nbox\Plugin\Mailbox;

use Drupal\nbox\Plugin\MailboxBase;
use Drupal\nbox\Plugin\MailboxRule;

/**
 * Provides an draft Mailbox.
 *
 * @Mailbox(
 *  id = "draft",
 *  label = @Translation("Draft"),
 *  weight = 7,
 *  showUnread = FALSE,
 * )
 */
class Draft extends MailboxBase {

  /**
   * {@inheritdoc}
   */
  public function setViewsFilterQuery() {
    $this->rules[] = new MailboxRule('deleted', 'not_deleted', '=');
    $this->rules[] = new MailboxRule('sender', TRUE, '=');
    $this->rules[] = new MailboxRule('draft', TRUE, '=');
  }

}
