<?php

namespace Drupal\niobi_admin\Plugin\niobi\access;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\niobi_admin\NiobiAccessInterface;

/**
 *
 * @NiobiAccess(
 *   id = "niobi_debug_access",
 *   label = @Translation("Debug Access"),
 *   description = @Translation("Always returns TRUE. Do not use on produciton sites."),
 * )
 */
class NiobiDebugAccess extends PluginBase implements NiobiAccessInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Always returns TRUE. Do not use on produciton sites.');
  }

  public static function determine_access($entity, $account) {
    /**
     * Always returns TRUE.
     */

    return TRUE;
  }

}