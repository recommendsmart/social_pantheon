<?php

namespace Drupal\crm_core_contact\Plugin\Field\FieldType;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'mail_with_type' field type.
 *
 * @FieldType(
 *   id = "email_with_type",
 *   label = @Translation("Email with type"),
 *   description = @Translation("An entity field containing an email and type values."),
 *   default_widget = "email_with_type",
 *   default_formatter = "email_with_type"
 * )
 */
class MailWithTypeItem extends EmailItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'email_types' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['type'] = DataDefinition::create('string')
      ->setLabel(t('Mail type'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['type'] = [
      'type' => 'varchar_ascii',
      'length' => 32,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $email_types = $this->getSetting('email_types');
    $element['email_types'] = [
      '#type' => 'textarea',
      '#title' => t('Email types'),
      '#default_value' => $this->emailTypesString($email_types),
      '#element_validate' => [[get_class($this), 'validateEmailTypes']],
      '#rows' => 3,
      '#description' => $this->t('Enter allowed email types in a "key|label" format.'),
      '#field_has_data' => $has_data,
      '#field_name' => $this->getFieldDefinition()->getName(),
      '#entity_type' => $this->getEntity()->getEntityTypeId(),
      '#email_types' => $email_types,
    ];

    return $element;
  }

  /**
   * Generates a string representation of an array of 'email_types'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected function emailTypesString($values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * #element_validate callback for possible mail types.
   */
  public static function validateMailTypes($element, FormStateInterface $form_state) {
    $values = static::extractMailTypes($element['#value']);

    if (!is_array($values)) {
      $form_state->setError($element, t('Email types list: invalid input.'));
    }
    else {
      // Check that keys are valid for the field type.
      foreach ($values as $key => $value) {
        if (Unicode::strlen($key) > 32) {
          $form_state->setError($element, 'Email types list: each key must be a string at most 32 characters long.');
          break;
        }
        if (preg_match('/[^a-z0-9]/', $key)) {
          $form_state->setError($element, 'Email types list: only international alphanumeric characters are allowed for keys.');
          break;
        }
      }

      // Prevent removing values currently in use.
      if ($element['#field_has_data']) {
        $lost_keys = array_keys(array_diff_key($element['#email_types'], $values));
        if (static::mailTypeInUse($element['#entity_type'], $element['#field_name'], $lost_keys)) {
          $form_state->setError($element, t('Email types list: some values are being removed while currently in use.'));
        }
      }

      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * Extracts the allowed values array from the mail_types element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   */
  protected static function extractMailTypes($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    return $values;
  }

  /**
   * Checks if any of the mail types are in use.
   *
   * @param string $entity_type
   *   ID of the entity type field belongs to.
   * @param string $field_name
   *   Name of the field.
   * @param array $email_types
   *   Array of mail type keys to check against.
   *
   * @return bool
   */
  protected static function mailTypeInUse($entity_type, $field_name, $email_types) {
    if ($email_types) {
      $factory = \Drupal::service('entity.query');
      $result = $factory->get($entity_type)
        ->condition($field_name . '.type', $email_types, 'IN')
        ->count()
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();
      if ($result) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
