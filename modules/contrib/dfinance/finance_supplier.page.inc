<?php

/**
 * @file
 * Contains financial_doc.page.inc.
 *
 * Page callback for Financial Document entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Supplier templates.
 *
 * Default template: finance_supplier.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_finance_supplier(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
