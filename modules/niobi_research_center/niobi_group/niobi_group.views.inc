<?php

/**
 * @file
 * Provide views data for add buttons.
 */

/**
 * Implements hook_views_data().
 */
function niobi_group_logic_views_data() {
  $data['groups']['niobi_department_add_button_field'] = [
    'title' => t('Department Add Button'),
    'help' => t('Displays a Department add button for the organization, if enabled.'),
    'field' => [
      'id' => 'niobi_department_add_button_field',
    ],
  ];
  $data['groups']['niobi_group_add_button_field'] = [
    'title' => t('Group Add Button'),
    'help' => t('Displays a Group add button for the department, if enabled.'),
    'field' => [
      'id' => 'niobi_group_add_button_field',
    ],
  ];
  $data['views']['niobi_department_add_button'] = [
      'title' => t('Department Add Button'),
      'help' => t('Displays a Department add button for the organization, if enabled.'),
      'area' => [
          'id' => 'niobi_department_add_button',
      ],
  ];
  $data['views']['niobi_group_add_button'] = [
      'title' => t('Group Add Button'),
      'help' => t('Displays a Group add button for the department, if enabled.'),
      'area' => [
          'id' => 'niobi_group_add_button',
      ],
  ];

  return $data;
}