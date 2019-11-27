<?php

namespace Drupal\smart_content\VariationSetType;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Base class for Smart variation plugins.
 */
abstract class VariationSetTypeBase extends PluginBase implements VariationSetTypeInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [
      'plugin_id' => $this->getPluginId(),
    ];
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // TODO: Implement calculateDependencies() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function writeChangesToConfiguration() {
    $configuration = $this->getConfiguration();
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function validateReactionRequest($variation_id, array $context = []) {
    return TRUE;
  }

  /**
   * Utility method for attaching and sorting Variations by weight.
   */
  public function attachTableVariationWeight($values) {
    foreach ($this->entity->getVariations() as $variation) {
      if (isset($values[$variation->id()]['weight'])) {
        $variation->setWeight($values[$variation->id()]['weight']);
      }
    }
    $this->entity->sortVariations();
  }

}
