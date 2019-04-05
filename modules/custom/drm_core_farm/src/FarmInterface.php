<?php

namespace Drupal\drm_core_farm;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Defines methods for DRM Farm entities.
 */
interface FarmInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
