<?php

/**
 * @file
 * Contains financial_doc.page.inc.
 *
 * Page callback for Financial Document entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Financial Document templates.
 *
 * Default template: financial_doc.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_financial_doc(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
