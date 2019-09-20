<?php

/**
 * @file
 * Drush integration for colorbox.
 */

/**
 * The Colorbox plugin URI.
 */
define('MULTIPLE_SELECT_DOWNLOAD_URI', 'https://github.com/wenzhixin/multiple-select/archive/master.zip');
define('MULTIPLE_SELECT_DOWNLOAD_PREFIX', 'multiple-select-');

/**
 * Implements hook_drush_command().
 */
function matrix_field_drush_command() {
  $items = array();

  // The key in the $items array is the name of the command.
  $items['matrix-field-plugins'] = array(
    'callback' => 'drush_matrix_field_plugins',
    'description' => dt('Download and install the Multiple Select plugin.'),
     // No bootstrap.
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => array(
      'path' => dt('Optional. A path where to install the Multiple Select plugin. If omitted Drush will use the default location.'),
    ),
    'aliases' => array('matrixfieldplugin'),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 *
 * This function is called whenever a drush user calls
 * 'drush help <name-of-your-command>'
 */
function matrix_field_drush_help($section) {
  switch ($section) {
    case 'drush:matrix-field-plugins':
      return dt('Download and install the Multiple Select plugin from wenzhixin/multiple-select, default location is the libraries directory.');
  }
}

/**
 * Command to download the Multiple Select plugin.
 */
function drush_matrix_field_plugins() {
  $args = func_get_args();
  if (!empty($args[0])) {
    $path = $args[0];
  }
  else {
    $path = 'libraries';
  }

  // Create the path if it does not exist.
  if (!is_dir($path)) {
    drush_op('mkdir', $path);
    drush_log(dt('Directory @path was created', array('@path' => $path)), 'notice');
  }

  // Set the directory to the download location.
  $olddir = getcwd();
  chdir($path);

  // Download the zip archive.
  if ($filepath = drush_download_file(MULTIPLE_SELECT_DOWNLOAD_URI)) {
    $filename = basename($filepath);
    $dirname = MULTIPLE_SELECT_DOWNLOAD_PREFIX . basename($filepath, '.zip');

    // Remove any existing Colorbox plugin directory.
    if (is_dir($dirname) || is_dir('multiple-select')) {
      drush_delete_dir($dirname, TRUE);
      drush_delete_dir('multiple-select', TRUE);
      drush_log(dt('A existing Multiple Select plugin was deleted from @path', array('@path' => $path)), 'notice');
    }

    // Decompress the zip archive.
    drush_tarball_extract($filename);

    // Change the directory name to "colorbox" if needed.
    if ($dirname !== 'multiple-select') {
      drush_move_dir($dirname, 'multiple-select', TRUE);
      $dirname = 'multiple-select';
    }
  }

  if (is_dir($dirname)) {
    drush_log(dt('Multiple Select plugin has been installed in @path', array('@path' => $path)), 'success');
  }
  else {
    drush_log(dt('Drush was unable to install the Multiple Select plugin to @path', array('@path' => $path)), 'error');
  }

  // Set working directory back to the previous working directory.
  chdir($olddir);
}
