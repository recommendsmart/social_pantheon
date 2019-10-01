<?php

namespace Drupal\nbox\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide a field for the most recent subject.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nbox_view_subject")
 */
class NboxViewSubject extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $subject = '';

    // First check the referenced entity.
    $nboxMetadata = $values->_entity;

    $type = get_class($nboxMetadata);
    if ($type === 'Drupal\nbox\Entity\NboxMetadata') {
      $subject = $nboxMetadata->getMostRecent()->getSubject();
    }

    return $subject;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
