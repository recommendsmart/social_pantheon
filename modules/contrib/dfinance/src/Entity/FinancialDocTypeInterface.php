<?php

namespace Drupal\dfinance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Financial Document type entities.
 */
interface FinancialDocTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the Financial Doc Type description.
   *
   * @return string
   *   Description of the Financial Doc Type.
   */
  public function getDescription();

  /**
   * Sets the Financial Doc Type description.
   *
   * @param string $description
   *   The Financial Doc Type description.
   *
   * @return \Drupal\dfinance\Entity\FinancialDocTypeInterface
   *   The called Organisation entity.
   */
  public function setDescription($description);

}
