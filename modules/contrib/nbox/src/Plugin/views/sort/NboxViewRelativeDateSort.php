<?php

namespace Drupal\nbox\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Sort handler ordering by most recent.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("nbox_view_relative_date_sort")
 */
class NboxViewRelativeDateSort extends SortPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->query->addOrderBy($this->tableAlias, 'most_recent', $this->options['order']);
  }

}
