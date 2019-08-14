<?php

namespace Drupal\clever_theme_switcher\Entity\Interfaces;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface defining an Cts entity.
 */
interface CtsInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {
  // Add get/set methods for your configuration properties here.
}
