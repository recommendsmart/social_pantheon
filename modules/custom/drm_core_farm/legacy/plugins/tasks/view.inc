<?php

/**

 * @file
 * Handle the 'drm_core_farm view' override task.
 *
 * This plugin overrides drm-core/farm/%drm_core_farm and reroutes it to the page manager, where
 * a list of tasks can be used to service this request based upon criteria
 * supplied by access plugins.
 */

/**
 * Specialized implementation of hook_page_manager_task_tasks(). See api-task.html for
 * more information.
 */
function drm_core_farm_view_page_manager_tasks() {
  return array(
    // This is a 'page' task and will fall under the page admin UI
    'task type' => 'page',

    'title' => t('Farm template'),

    'admin title' => t('Farm template'),
    'admin description' => t('When enabled, this overrides the default Drupal behavior for displaying farms at <em>drm-core/farm/%drm_core_farm</em>. If you add variants, you may use selection criteria such as farm type or language or user access to provide different views of farms. If no variant is selected, the default Drupal farm view will be used. This page only affects farms viewed as pages, it will not affect farms viewed in lists or at other locations. Also please note that if you are using pathauto, aliases may make a farm to be somewhere else, but as far as Drupal is concerned, they are still at drm-core/farm/%drm_core_farm.'),
    'admin path' => 'drm-core/farm/%drm_core_farm',

    // Menu hooks so that we can alter the drm-core/farm/%drm_core_farm menu entry to point to us.
    'hook menu' => 'drm_core_farm_view_menu',
    'hook menu alter' => 'drm_core_farm_view_menu_alter',

    // This is task uses 'context' handlers and must implement these to give the
    // handler data it needs.
    'handler type' => 'context',
    'get arguments' => 'drm_core_farm_view_get_arguments',
    'get context placeholders' => 'drm_core_farm_view_get_contexts',

    // Allow this to be enabled or disabled:
    'disabled' => variable_get('page_manager_drm_core_farm_view_disabled', TRUE),
    'enable callback' => 'drm_core_farm_view_enable',
    'access callback' => 'drm_core_farm_view_access_check',
  );
}

/**
 * Callback defined by drm_core_farm_view_page_manager_tasks().
 *
 * Alter the drm_core_farm view input so that drm_core_farm view comes to us rather than the
 * normal drm_core_farm view process.
 */
function drm_core_farm_view_menu_alter(&$items, $task) {
  if (variable_get('page_manager_drm_core_farm_view_disabled', TRUE)) {
    return;
  }

  // Override the drm_core_farm view handler for our purpose.
  $callback = $items['drm-core/farm/%drm_core_farm']['page callback'];
  if ($callback == 'drm_core_farm_view' || variable_get('page_manager_override_anyway', FALSE)) {
    $items['drm-core/farm/%drm_core_farm']['page callback'] = 'drm_core_farm_view_page';
    $items['drm-core/farm/%drm_core_farm']['file path'] = $task['path'];
    $items['drm-core/farm/%drm_core_farm']['file'] = $task['file'];
  }
  else {
    // automatically disable this task if it cannot be enabled.
    variable_set('page_manager_drm_core_farm_view_disabled', TRUE);
    if (!empty($GLOBALS['page_manager_enabling_drm_core_farm_view'])) {
      drupal_set_message(t('Page manager module is unable to enable drm-core/farm/%drm_core_farm because some other module already has overridden with %callback.', array('%callback' => $callback)), 'error');
    }
  }

  // @todo override drm_core_farm revision handler as well?
}

/**
 * Entry point for our overridden drm_core_farm view.
 *
 * This function asks its assigned handlers who, if anyone, would like
 * to run with it. If no one does, it passes through to Drupal core's
 * drm_core_farm view, which is drm_core_farm().
 */
function drm_core_farm_view_page($drm_core_farm) {
  // Load my task plugin
  $task = page_manager_get_task('view');

  // Load the drm_core_farm into a context.
  ctools_include('context');
  ctools_include('context-task-handler');

  // We need to mimic Drupal's behavior of setting the drm_core_farm title here.
  drupal_set_title(drm_core_farm_title($drm_core_farm));
  $uri = entity_uri('drm_core_farm', $drm_core_farm);
  // Set the drm_core_farm path as the canonical URL to prevent duplicate content.
  drupal_add_html_head_link(array('rel' => 'canonical', 'href' => url($uri['path'], $uri['options'])), TRUE);
  // Set the non-aliased path as a default shortlink.
  drupal_add_html_head_link(array('rel' => 'shortlink', 'href' => url($uri['path'], array_merge($uri['options'], array('alias' => TRUE)))), TRUE);
  $contexts = ctools_context_handler_get_task_contexts($task, '', array($drm_core_farm));

  $output = ctools_context_handler_render($task, '', $contexts, array($drm_core_farm->farm_id));
  if ($output != FALSE) {
    return $output;
  }

  $function = 'drm_core_farm_view';
  foreach (module_implements('page_manager_override') as $module) {
    $call = $module . '_page_manager_override';
    if (($rc = $call('drm_core_farm_view')) && function_exists($rc)) {
      $function = $rc;
      break;
    }
  }

  // Otherwise, fall back.
  return $function($drm_core_farm);
}

/**
 * Callback to get arguments provided by this task handler.
 *
 * Since this is the drm_core_farm view and there is no UI on the arguments, we
 * create dummy arguments that contain the needed data.
 */
function drm_core_farm_view_get_arguments($task, $subtask_id) {
  return array(
    array(
      'keyword' => 'drm_core_farm',
      'identifier' => t('Farm being viewed'),
      'id' => 1,
      'name' => 'entity_id:drm_core_farm',
      'settings' => array(),
    ),
  );
}

/**
 * Callback to get context placeholders provided by this handler.
 */
function drm_core_farm_view_get_contexts($task, $subtask_id) {
  return ctools_context_get_placeholders_from_argument(page_manager_drm_core_farm_view_get_arguments($task, $subtask_id));
}

/**
 * Callback to enable/disable the page from the UI.
 */
function drm_core_farm_view_enable($cache, $status) {
  variable_set('page_manager_drm_core_farm_view_disabled', $status);

  // Set a global flag so that the menu routine knows it needs
  // to set a message if enabling cannot be done.
  if (!$status) {
    $GLOBALS['page_manager_enabling_drm_core_farm_view'] = TRUE;
  }
}

/**
 * Callback to determine if a page is accessible.
 *
 * @param $task
 *   The task plugin.
 * @param $subtask_id
 *   The subtask id
 * @param $contexts
 *   The contexts loaded for the task.
 * @return
 *   TRUE if the current user can access the page.
 */
function drm_core_farm_view_access_check($task, $subtask_id, $contexts) {
  $context = reset($contexts);
  return drm_core_farm_access('view', $context->data);
}
