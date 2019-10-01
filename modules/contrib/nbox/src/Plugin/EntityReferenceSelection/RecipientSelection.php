<?php

namespace Drupal\nbox\Plugin\EntityReferenceSelection;

use Drupal\user\Plugin\EntityReferenceSelection\UserSelection;

/**
 * Entity Reference Selection for recipients.
 *
 * @EntityReferenceSelection(
 *   id = "nbox:user",
 *   label = @Translation("Recipient selection"),
 *   base_plugin_label = @Translation("Recipient selection"),
 *   entity_types = {"user"},
 *   group = "nbox",
 *   weight = 0
 * )
 */
class RecipientSelection extends UserSelection {

}
