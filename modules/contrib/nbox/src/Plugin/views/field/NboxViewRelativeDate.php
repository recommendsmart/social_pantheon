<?php

namespace Drupal\nbox\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide a field for the relative date.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nbox_view_relative_date")
 */
class NboxViewRelativeDate extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $relativeDate = '';

    // First check the referenced entity.
    $nboxMetadata = $values->_entity;

    $type = get_class($nboxMetadata);
    if ($type === 'Drupal\nbox\Entity\NboxMetadata') {
      $relativeDate = $nboxMetadata->getMostRecent()->getSentTimeRelative();
    }

    return $relativeDate;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function clickSort($order) {
    $params = $this->options['group_type'] != 'group' ? ['function' => $this->options['group_type']] : [];
    // Most recent is the incremental nbox message ID and thus highest ID, is
    // also most recent.
    $this->query->addOrderBy($this->tableAlias, 'most_recent', $order, $this->field_alias, $params);
  }

}
