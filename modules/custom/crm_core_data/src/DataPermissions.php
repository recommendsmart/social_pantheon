<?php

namespace Drupal\crm_core_data;

use Drupal\crm_core\CRMCorePermissions;

/**
 * Class DataPermissions.
 */
class DataPermissions {

  /**
   * Returns Business permissions.
   *
   * @return array
   *   CRM Business permissions.
   */
  public function permissions() {
    $perm_builder = new CRMCorePermissions();

    return array_merge($perm_builder->entityTypePermissions('crm_core_record'));
  }

}
