<?php

/**
 * @file
 * Contains niobi_whitelist_item.page.inc.
 *
 * Page callback for Whitelist Item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Whitelist Item templates.
 *
 * Default template: niobi_whitelist_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_niobi_whitelist_item(array &$variables) {
  // Fetch NiobiWhitelistItem Entity Object.
  $niobi_whitelist_item = $variables['elements']['#niobi_whitelist_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
