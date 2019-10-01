<?php

namespace Drupal\nbox\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for pluggable Mailboxes.
 *
 * @see \Drupal\nbox\Annotation\Mailbox
 * @see \Drupal\nbox\Plugin\MailboxBase
 * @see plugin_api
 */
interface MailboxInterface extends PluginInspectionInterface {

  /**
   * Set the views Filter query.
   */
  public function setViewsFilterQuery();

}
