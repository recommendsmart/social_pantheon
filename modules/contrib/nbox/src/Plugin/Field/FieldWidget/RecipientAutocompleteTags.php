<?php

namespace Drupal\nbox\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;

/**
 * Plugin implementation of the 'recipient_autocomplete_tags' widget.
 *
 * @FieldWidget(
 *   id = "recipient_autocomplete_tags",
 *   label = @Translation("Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete text field."),
 *   field_types = {
 *     "recipient",
 *   },
 *   multiple_values = TRUE
 * )
 */
class RecipientAutocompleteTags extends EntityReferenceAutocompleteTagsWidget {

}
