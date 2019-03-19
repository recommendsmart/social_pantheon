<?php
/**
 * @file
 * Contains \Drupal\redhen_liability\LiabilityPermissions.
 */


namespace Drupal\redhen_liability;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\redhen_liability\Entity\LiabilityType;

class LiabilityPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of RedHen liability type permissions.
   *
   * @return array
   *    Returns an array of permissions.
   */
  public function LiabilityTypePermissions() {
    $perms = [];
    // Generate liability permissions for all liability types.
    foreach (LiabilityType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Builds a standard list of permissions for a given liability type.
   *
   * @param \Drupal\redhen_liability\Entity\LiabilityType $liability_type
   *   The machine name of the liability type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(LiabilityType $liability_type) {
    $type_id = $liability_type->id();
    $type_params = ['%type' => $liability_type->label()];

    return [
      "add $type_id liability" => [
        'title' => $this->t('%type: Add liability', $type_params),
      ],
      "view active $type_id liability" => [
        'title' => $this->t('%type: View active liabilities', $type_params),
      ],
      "view inactive $type_id liability" => [
        'title' => $this->t('%type: View inactive liabilities', $type_params),
      ],
      "edit $type_id liability" => [
        'title' => $this->t('%type: Edit liability', $type_params),
      ],
      "delete $type_id liability" => [
        'title' => $this->t('%type: Delete liability', $type_params),
      ],
    ];
  }

}
