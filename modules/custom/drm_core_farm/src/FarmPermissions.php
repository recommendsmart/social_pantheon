<?php

namespace Drupal\drm_core_farm;

use Drupal\drm_core\DRMCorePermissions;

/**
 * Class FarmPermissions.
 */
class FarmPermissions {

  /**
   * Returns Record and Business permissions.
   *
   * @return array
   *   DRM Core Record and Business permissions.
   */
  public function permissions() {
    $perm_builder = new DRMCorePermissions();

    return array_merge($perm_builder->entityTypePermissions('drm_core_record'), $perm_builder->entityTypePermissions('drm_core_business'));
  }

}
