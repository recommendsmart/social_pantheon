<?php

namespace Drupal\crm_core_farm;

/**
 * Defines methods for CRM Farm Type entities.
 */
interface FarmTypeInterface {

  /**
   * Returns the human readable name of any or all farm types.
   *
   * @return array
   *   An array containing all human readable names keyed on the machine type.
   */
  public static function getNames();

}
