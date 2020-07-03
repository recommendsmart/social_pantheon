<?php

namespace Drupal\modal_page\Helper;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Modal Page Field Helper.
 */
class ModalPageFieldHelper {

  /**
   * Get Field Role.
   */
  public function getFieldRole() {

    $fieldRoles = BaseFieldDefinition::create('list_string');
    $fieldRoles->setLabel(t('Who can access this Modal'));
    $fieldRoles->setSettings(['allowed_values' => user_role_names()]);
    $fieldRoles->setDescription(t('Do you want to restrict this Modal by role? If no role is selected this Modal will be visible to everyone.'));
    $fieldRoles->setRequired(FALSE);
    $fieldRoles->setCardinality(-1);
    $fieldRoles->setDisplayOptions('form', [
      'type' => 'options_buttons',
      'weight' => 6,
    ]);
    $fieldRoles->setDisplayConfigurable('form', TRUE);

    return $fieldRoles;
  }

}
