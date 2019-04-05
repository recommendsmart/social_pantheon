<?php

namespace Drupal\drm_core_record\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'mail_with_type' formatter.
 *
 * @FieldFormatter(
 *   id = "email_with_type",
 *   label = @Translation("Email with type as plain text"),
 *   field_types = {
 *     "email_with_type",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class MailWithTypeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $email_types = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('email_types');
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $item->value,
        '#url' => Url::fromUri('mailto:' . $item->value),
        '#prefix' => $email_types[$item->type] . ': ',
      ];
    }
    return $elements;
  }

}
