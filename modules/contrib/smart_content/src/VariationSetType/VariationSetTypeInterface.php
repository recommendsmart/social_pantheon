<?php

namespace Drupal\smart_content\VariationSetType;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for SmartVariationSetType plugins.
 */
interface VariationSetTypeInterface extends PluginInspectionInterface {

  /**
   * Checks if the reaction is accessible and valid.
   *
   * E.g. checking if the user has access to view the reaction entity.
   *
   * @param string $variation_id
   *   Id of variation.
   * @param array $context
   *   Context provided by request for reaction.
   *
   * @return bool
   *   Results of validation.
   */
  public function validateReactionRequest($variation_id, array $context);

}
