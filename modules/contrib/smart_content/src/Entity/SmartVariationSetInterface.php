<?php

namespace Drupal\smart_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\smart_content\Variation\VariationInterface;
use Drupal\smart_content\VariationSetType\VariationSetTypeInterface;

/**
 * Provides an interface for defining Smart variation set entities.
 */
interface SmartVariationSetInterface extends ConfigEntityInterface {

  /**
   * Gets the wrapper object for this entity.
   *
   * @return \Drupal\smart_content\VariationSetType\VariationSetTypeInterface|null
   *   A VariationSetType instance.
   */
  public function getVariationSetType();

  /**
   * Sets a reference to the wrapper object for this entity.
   *
   * @param \Drupal\smart_content\VariationSetType\VariationSetTypeInterface $variation_set_type
   *   A VariationSetType instance.
   */
  public function setVariationSetType(VariationSetTypeInterface $variation_set_type);

  /**
   * Adds a variation to this variation set.
   *
   * @param \Drupal\smart_content\Variation\VariationInterface $variation
   *   A Variation instance.
   */
  public function addVariation(VariationInterface $variation);

  /**
   * Gets the variations that are a part of this variation set.
   *
   * @return \Drupal\smart_content\Variation\VariationInterface[]
   *   Array of variation instances.
   */
  public function getVariations();

  /**
   * Gets the specified variation from this variation set.
   *
   * @param string $id
   *   Variation ID.
   *
   * @return \Drupal\smart_content\Variation\VariationInterface
   *   A Variation instance.
   */
  public function getVariation($id);

  /**
   * Removes the specified variation from this variation set.
   *
   * @param string $id
   *   Variation ID.
   */
  public function removeVariation($id);

}
