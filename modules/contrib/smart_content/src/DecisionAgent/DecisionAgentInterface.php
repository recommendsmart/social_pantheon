<?php

namespace Drupal\smart_content\DecisionAgent;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Smart decision agent plugins.
 */
interface DecisionAgentInterface extends PluginInspectionInterface {

  /**
   * Returns placeholder for associated javascript to search for.
   *
   * @param array $context
   *   Array of context values for rendering.
   *
   * @return mixed
   *   Render array.
   */
  public function renderPlaceholder(array $context);

  /**
   * Returns required JS libraries for this type.
   *
   * @return array
   *   An array of Drupal libraries.
   */
  public function getLibraries();

}
