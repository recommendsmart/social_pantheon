<?php

namespace Drupal\nbox\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide a field for the recipient summary.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nbox_view_recipient_summary")
 */
class NboxViewRecipientSummary extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $senders = '';

    /** @var \Drupal\nbox\Entity\NboxMetadata $nboxMetadata */
    $nboxMetadata = $values->_entity;
    $type = get_class($nboxMetadata);
    if ($type === 'Drupal\nbox\Entity\NboxMetadata') {
      $suffix = '';
      if ($nboxMetadata->getMessageCount() > 1) {
        $suffix = ' (' . $nboxMetadata->getMessageCount() . ')';
      }
      $senders = $nboxMetadata->getSummary(TRUE, FALSE) . $suffix;
    }

    return $senders;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
