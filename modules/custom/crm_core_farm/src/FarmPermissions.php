<?php

namespace Drupal\crm_core_farm;

use Drupal\crm_core\CRMCorePermissions;

/**
 * Class FarmPermissions.
 */
class FarmPermissions {

  /**
   * Returns Record and Business permissions.
   *
   * @return array
   *   CRM Core Record and Business permissions.
   */
  public function permissions() {
    $perm_builder = new CRMCorePermissions();

    return array_merge($perm_builder->entityTypePermissions('crm_core_record'), $perm_builder->entityTypePermissions('crm_core_business'));
  }

}
