<?php

namespace Drupal\nbox\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'recipient label' formatter.
 *
 * @FieldFormatter(
 *   id = "recipient_label",
 *   label = @Translation("Label"),
 *   description = @Translation("Display the label of the referenced recipients."),
 *   field_types = {
 *     "recipient"
 *   }
 * )
 */
class RecipientLabelFormatter extends EntityReferenceLabelFormatter {

}
