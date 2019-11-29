<?php

namespace Drupal\agerp_core_basic;

use Drupal\agerp_core\AGERPCorePermissions;

/**
 * Class BasicPermissions.
 */
class BasicPermissions {

  /**
   * Returns Party and Resource permissions.
   *
   * @return array
   *   AGERP Core Party and Resource permissions.
   */
  public function permissions() {
    $perm_builder = new AGERPCorePermissions();

    return array_merge($perm_builder->entityTypePermissions('agerp_core_party'));
  }

}
