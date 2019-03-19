<?php

/**
 * @file
 * Describes API functions for the RedHen Liability module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the display name for a liability.
 *
 * @param string $name
 *   The generated name.
 * @param Drupal\redhen_liability\LiabilityInterface $liability
 *   The liability whose name is being generated.
 *
 * @return string
 */
function hook_redhen_liability_name_alter(&$name, Drupal\redhen_liability\LiabilityInterface $liability) {
  return $liability->get('last_name')->value . ', ' . $liability->get('first_name')->value;
}

/**
 * @} End of "addtogroup hooks".
 */
