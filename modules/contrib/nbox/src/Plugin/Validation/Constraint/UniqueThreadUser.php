<?php

namespace Drupal\nbox\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted values form an unique thread / user pair.
 *
 * @Constraint(
 *   id = "UniqueThreadUser",
 *   label = @Translation("Unique Thread User", context = "Validation"),
 *   type = "entity:nbox_metadata"
 * )
 */
class UniqueThreadUser extends Constraint {

  /**
   * Message to show van unique index is not unique.
   *
   * @var string
   */
  public $notUniquePair = 'The thread_id / user_id combination is not unique';

}
