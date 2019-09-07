<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\currency\Entity\CurrencyInterface;

/**
 * Provides an interface for defining Organisation entities.
 *
 * @ingroup dfinance
 */
interface OrganisationInterface extends ContentEntityInterface {

  /**
   * Gets the Organisation name.
   *
   * @return string
   *   Name of the Organisation.
   */
  public function getName();

  /**
   * Sets the Organisation name.
   *
   * @param string $name
   *   The Organisation name.
   *
   * @return \Drupal\dfinance\Entity\OrganisationInterface
   *   The called Organisation entity.
   */
  public function setName($name);

  /**
   * Gets the Currency Entity that this Organisation uses.
   *
   * @return \Drupal\currency\Entity\CurrencyInterface
   *   The Currency Entity.
   */
  public function getCurrency();

  /**
   * Gets the Currency Entity ID that this Organisation uses.
   *
   * @return string
   *   The Currency Entity ID.
   */
  public function getCurrencyId();

  /**
   * This method is experimental and may be changed or removed
   * @todo identify if it's safe to allow the currency to be changed
   *
   * Sets the Currency Entity that this Organisation uses.
   *
   * @param \Drupal\currency\Entity\CurrencyInterface
   *   The Currency Entity.
   *
   * @return \Drupal\dfinance\Entity\OrganisationInterface
   *   The called Organisation entity.
   */
  public function setCurrency(CurrencyInterface $currency);

  /**
   * This method is experimental and may be changed or removed
   * @todo identify if it's safe to allow the currency to be changed
   *
   * Sets the Currency Entity ID that this Organisation uses.
   *
   * @param string
   *   The Currency Entity ID.
   *
   * @return \Drupal\dfinance\Entity\OrganisationInterface
   *   The called Organisation entity.
   */
  public function setCurrencyId($currency);

}
