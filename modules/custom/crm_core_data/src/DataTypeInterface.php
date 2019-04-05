<?php

namespace Drupal\crm_core_data;

/**
 * Defines methods for CRM Data Type entities.
 */
interface DataTypeInterface {

  /**
   * Returns the human readable name of any or all data types.
   *
   * @return array
   *   An array containing all human readable names keyed on the machine type.
   */
  public static function getNames();

}
