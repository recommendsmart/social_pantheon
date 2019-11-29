<?php

namespace Drupal\agerp_core_basic;

/**
 * Defines methods for AGERP Basic Type entities.
 */
interface BasicTypeInterface {

  /**
   * Returns the human readable name of any or all basic types.
   *
   * @return array
   *   An array containing all human readable names keyed on the machine type.
   */
  public static function getNames();

}
