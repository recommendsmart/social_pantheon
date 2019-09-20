<?php

namespace Drupal\matrix_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Plugin implementation of the 'matrix_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "matrix_field_formatter",
 *   label = @Translation("Matrix Field Formatter"),
 *   field_types = {
 *     "matrix_field"
 *   }
 * )
 */
class MatrixFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fieldStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $groupStorage;

  /**
   * MatrixFieldFormatter constructor.
   *
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param string $label
   * @param string $view_mode
   * @param array $third_party_settings
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->fieldStorage = $entityTypeManager->getStorage('matrix_field');
    $this->groupStorage = $entityTypeManager->getStorage('matrix_field_group');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (!$items->count()) {
      return [];
    }
    $elements = [
      '#theme' => 'matrix_field',
    ];
    $fields = $this->fieldStorage->loadMultiple();
    $groups = $this->groupStorage->loadMultiple();
    // TODO: check if it possible to replace it by weight.
    uasort($groups, '_matrix_field_sort_fields');
    $map = [];
    $types = [];
    foreach ($fields as $field) {
      $map[$field->id()] = $field->label();
      $types[$field->id()] = $field->get('field_type');
    }

    // Field groups.
    $grouped_fields = [];

    foreach ($items as $delta => $item) {
      if (!isset($fields[$item->field_id])) {
        continue;
      }
      $field = $fields[$item->field_id];
      if ($types[$item->field_id] === 'boolean') {
        // Process boolean values to human-readable.
        $value = $item->field_value == 1 ? $this->t('Yes') : $this->t('No');
      } else {
        $value = $item->field_value;
      }
      if (empty($field->get('parent'))) {
        $elements[$delta] = [
          'name' => $map[$item->field_id],
          'value' => $value,
          'unit' => $field->get('unit'),
          'description' => $field->get('description'),
          '#weight' => $field->get('weight'),
        ];
      } else {
        $group = $groups[$field->get('parent')];
        if (!isset($grouped_fields[$field->get('parent')])) {
          $grouped_fields[$field->get('parent')] = [
            'title' => $group->label(),
            'items' => [],
          ];
        }
        $grouped_fields[$field->get('parent')]['items'][] = [
          'name' => $map[$item->field_id],
          'value' => $value,
          'unit' => $field->get('unit'),
          'description' => $field->get('description'),
          '#weight' => $field->get('weight'),
        ];
      }
    }
    $elements['#groups'] = $grouped_fields;
    return $elements;
  }

}
