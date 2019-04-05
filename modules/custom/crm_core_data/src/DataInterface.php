<?php

namespace Drupal\crm_core_data;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines methods for CRM Data entities.
 */
interface DataInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
