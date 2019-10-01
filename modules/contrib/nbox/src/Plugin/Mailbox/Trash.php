<?php

namespace Drupal\nbox\Plugin\Mailbox;

use Drupal\nbox\Plugin\MailboxBase;
use Drupal\nbox\Plugin\MailboxRule;

/**
 * Provides an trash Mailbox.
 *
 * @Mailbox(
 *  id = "trash",
 *  label = @Translation("Trash"),
 *  weight = 10,
 *  showUnread = TRUE,
 * )
 */
class Trash extends MailboxBase {

  /**
   * {@inheritdoc}
   */
  public function setViewsFilterQuery() {
    $this->rules[] = new MailboxRule('deleted', 'marked_for_deletion', '=');
  }

}
