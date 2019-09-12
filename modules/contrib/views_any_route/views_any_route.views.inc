<?php

/**
 * @file
 * Views area plugin info for views_any_route.
 */

/**
 * Implements hook_views_data_alter().
 */
function views_any_route_views_data_alter(array &$data) {
  $data['views']['views_any_route_area'] = [
    'title' => t('Any Route Button'),
    'help' => t('Render a button based on a Drupal route.'),
    'area' => ['id' => 'views_any_route_area'],
  ];
  $data['views']['views_any_route_field'] = [
    'title' => t('Any Route Button'),
    'help' => t('Render a button based on a Drupal route'),
    'field' => ['id' => 'views_any_route_field'],
  ];
}
