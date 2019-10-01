<?php

namespace Drupal\nbox\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a mailbox annotation object.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class Mailbox extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin weight.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * If the mailbox should show a unread count.
   *
   * @var bool
   */
  public $showUnread = FALSE;

}
