<?php

namespace Drupal\nbox\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'recipient' field type.
 *
 * @FieldType(
 *   id = "recipient",
 *   label = @Translation("Recipient"),
 *   description = @Translation("This field stores the ID of a recipient as an integer value."),
 *   category = @Translation("Reference"),
 *   default_widget = "recipient_autocomplete_tags",
 *   default_formatter = "recipient_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class Recipient extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $storage = parent::defaultStorageSettings();
    $storage['target_type'] = 'user';
    return $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $form['handler']['handler_settings']['include_anonymous']['#default_value'] = 0;
    unset($form['handler']['handler_settings']['include_anonymous'], $form['handler']['handler_settings']['auto_create']);

    $recipient_type = $this->getSettings()['handler_settings']['recipient_type'] ?? 'to';
    $form['handler']['handler_settings']['recipient_type'] = [
      '#type' => 'radios',
      '#title' => t('Recipient type'),
      '#description' => t('Type of recipient determines visibility.'),
      '#options' => [
        'to' => $this->t('Recipient (To)'),
        'cc' => $this->t('Carbon copy (cc)'),
        'bcc' => $this->t('Blind carbon copy (bcc)'),
      ],
      '#default_value' => $recipient_type,
      '#weight' => 1,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    return [];
  }

}
