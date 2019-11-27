<?php

namespace Drupal\smart_content\Condition;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Smart condition plugins.
 */
interface ConditionInterface extends PluginInspectionInterface {

  /**
   * Writes changes from Condition instance to configuration array.
   */
  public function writeChangesToConfiguration();

  /**
   * Returns an array of JS settings.
   *
   * @return array
   *   An array of JS settings.
   */
  public function getAttachedSettings();

}
