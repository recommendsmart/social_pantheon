<?php

namespace Drupal\niobi_whitelist\Plugin\niobi\access;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\niobi_admin\NiobiAccessInterface;

/**
 *
 * @NiobiAccess(
 *   id = "niobi_whitelist",
 *   label = @Translation("Whitelist Access"),
 *   description = @Translation("Allows access to a given entity on the basis of being in a whitelist."),
 * )
 */
class NiobiWhitelistAccess extends PluginBase implements NiobiAccessInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Allows access to a given entity on the basis of being in a whitelist.');
  }

  public static function determine_access($entity, $account) {
    /**
     * TODO: Write the plugin
     */

    return TRUE;
  }

}