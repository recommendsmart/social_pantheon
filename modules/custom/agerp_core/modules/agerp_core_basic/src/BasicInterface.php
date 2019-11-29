<?php

namespace Drupal\agerp_core_basic;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines methods for AGERP Basic entities.
 */
interface BasicInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
