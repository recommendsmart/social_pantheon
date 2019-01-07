<?php

/**
 * @file
 * Provide views data for add buttons.
 */

/**
 * Implements hook_views_data().
 */
function niobi_research_center_knowledge_base_views_data() {
  $data['views']['niobi_research_center_kb_add_button'] = [
      'title' => t('KB Add Button'),
      'help' => t('Displays a KB add button for the group, if enabled.'),
      'area' => [
          'id' => 'niobi_research_center_kb_add_button',
      ],
  ];
  return $data;
}