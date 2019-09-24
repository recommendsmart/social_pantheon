<?php

namespace Drupal\dfinance\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint;
use Symfony\Component\Validator\Constraint;

/**
 * Checks if an entity field has a unique value.
 *
 * @todo Remove once https://www.drupal.org/project/drupal/issues/3080972 is fixed
 *
 * @Constraint(
 *   id = "dfinance_account_code",
 *   label = @Translation("Financial Account Code", context = "Validation"),
 *   type = "string"
 * )
 */
class AccountCodeIdConstraint extends UniqueFieldConstraint {

  /**
   * {@inheritDoc}
   */
  public function validatedBy() {
    return AccountCodeIdValidator::class;
  }

}