<?php

namespace Drupal\crm_core_farm;

use Drupal\crm_core\CRMCorePermissions;

/**
 * Class FarmPermissions.
 */
class FarmPermissions {

  /**
   * Returns Business permissions.
   *
   * @return array
   *   CRM Business permissions.
   */
  public function permissions() {
    $perm_builder = new CRMCorePermissions();

    return array_merge($perm_builder->entityTypePermissions('crm_core_business'));
  }

}
