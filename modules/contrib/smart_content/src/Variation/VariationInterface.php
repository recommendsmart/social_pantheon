<?php

namespace Drupal\smart_content\Variation;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Smart variation plugins.
 */
interface VariationInterface extends PluginInspectionInterface {

  /**
   * Write VariationBase instances to configuration array.
   *
   * This is generally called before the entity is saved to turn instantiated
   * Variations back to configuration.
   */
  public function writeChangesToConfiguration();

}
