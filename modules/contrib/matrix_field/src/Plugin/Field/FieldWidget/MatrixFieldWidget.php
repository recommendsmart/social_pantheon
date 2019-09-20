<?php

namespace Drupal\matrix_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Plugin implementation of the 'matrix_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "matrix_field_widget",
 *   label = @Translation("Matrix Field widget"),
 *   field_types = {
 *     "matrix_field"
 *   }
 * )
 */
class MatrixFieldWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $configStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fieldStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $groupStorage;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface
    $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->configStorage = $entity_type_manager->getStorage('matrix_field_matrix');
    $this->fieldStorage = $entity_type_manager->getStorage('matrix_field');
    $this->groupStorage = $entity_type_manager->getStorage('matrix_field_group');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $conf = $this->configStorage->loadMultiple();
    $options = [];
    foreach ($conf as $c) {
      $options[$c->id()] = $c->label();
    }
    $keys = array_keys($options);
    $current_matrix = $form_state->get('current_matrix');
    $list = reset($items);
    if ($current_matrix === NULL) {
      if (!empty($list)) {
        $current_matrix = reset($list)->matrix;
      } else {
        $current_matrix = reset($keys);
      }
    }
    $fields = $this->fieldStorage->loadMultiple();
    // Remove unneeded items.
    foreach ($fields as $field_id => $field) {
      $cols = $field->get('matrices') ?? [];
      if (!in_array($current_matrix, $cols, FALSE)) {
        unset($fields[$field_id]);
      }
    }
    // TODO: check if it possible to replace it by weight.
    uasort($fields, '_matrix_field_sort_fields');
    $groups = $this->groupStorage->loadMultiple();
    uasort($groups, '_matrix_field_sort_fields');
    // Sort fields with groups respecting.
    foreach ($groups as $group_key => $group) {
      foreach ($fields as $key => $field) {
        if($field->get('parent') === $group_key) {
          if (!isset($fields[$group_key])) {
            $fields[$group_key] = $group;
          }
          unset($fields[$key]);
          $fields[$key] = $field;
        }
      }
    }
    $elements = [
      '#type' => 'details',
      '#title' => $this->fieldDefinition->getLabel(),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#collapsed' => FALSE,
    ];
    // Field description.
    $elements['description'] = [
      '#weight' => 98,
      '#markup' => FieldFilteredMarkup::create(\Drupal::token()->replace($this->fieldDefinition->getDescription())),
    ];
    // Links to add new Matrix and Field.
    if ($this->currentUser->hasPermission('configure matrix field')) {
      $add_matrix_url = Url::fromRoute('entity.matrix_field_matrix.add_form');
      $add_field_url = Url::fromRoute('matrix_field.matrix_fields_form');
      $elements['legend'] = [
        '#weight' => 99,
        '#markup' => new TranslatableMarkup('You can add new <a href="@matrix_link"
target="_blank">matrix</a> or <a href="@field_link" target="_blank">
          field</a> if needed.', [ '@matrix_link' => $add_matrix_url->toString(),
          '@field_link' => $add_field_url->toString(),]),
      ];
    }

    $elements['select_matrix'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Matrix'),
      '#weight' => -30,
      '#options' => $options,
      '#default_value' => $current_matrix,
      '#ajax' => [
        'callback' => [$this, 'updateMatrix'],
        'wrapper' => 'matrix-field-wrapper',
      ],
      '#element_validate' => [[$this, 'validateMatrix']],
    ];
    $elements['matrix_field_items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('Value'),
        '',
      ],
      '#prefix' => '<div id="matrix-field-wrapper">',
      '#suffix' => '</div>',
    ];
    $delta = 0;
    foreach ($fields as $field) {
      // Handle Matrix Field groups.
      if ($field->getEntityTypeId() === 'matrix_field_group') {
        $elements['matrix_field_items'][$field->id()] = [
          [
            '#markup' => '<strong>' . $field->label() . '</strong>'
          ],
          []
        ];
      } else {
        $field_value = NULL;
        foreach ($list as $var) {
          if ($var->field_id === $field->id()) {
            $field_value = $var->field_value;
            break;
          }
        }
        if ($field->get('field_type') === 'list') {
          $allowed = $field->get('allowed_values');
          foreach ($allowed as $key => $value) {
            $allowed[$key] = trim($value);
          }
          $options = array_combine($allowed, $allowed);
          $field_element = [
            '#type' => 'select',
            '#options' => $options,
            '#default_value' => $field_value,
          ];
        } elseif ($field->get('field_type') === 'number') {
          $field_element = [
            '#type' => 'number',
            '#default_value' => $field_value,
            '#step' => '0.001',
          ];
        } elseif ($field->get('field_type') === 'boolean') {
          $field_element = [
            '#type' => 'checkbox',
            '#default_value' => $field_value,
          ];
        } else {
          $field_element = [
            '#type' => 'textfield',
            '#default_value' => $field_value,
          ];
        }
        $elements['matrix_field_items'][$field->id()] = [
          '#type' => 'container',
          '#delta' => $delta,
          '#weight' => $delta,
          'matrix_field_label' => [
            '#type' => 'container',
            'label' => [
              '#markup' => $field->label(),
            ],
            'field_id' => [
              '#type' => 'hidden',
              '#value' => $field->id(),
            ],
            'matrix' => [
              '#type' => 'hidden',
              '#value' => $current_matrix,
            ],
          ],
          'field_value' => $field_element,
          'unit' => [
            '#markup' => $field->get('unit'),
          ],
        ];
        $delta++;
      }

    }
    return $elements;
  }

  public function validateMatrix(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $field = $trigger['#array_parents'][0];
    $form_state->set('current_matrix', $form_state->getValue([$field, 'select_matrix']));
    if (isset($trigger['#type']) && $trigger['#type'] === 'select') {
      $form_state->setRebuild();
    }
  }

  public function updateMatrix(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $parent_key = $trigger['#array_parents'][0];
    return $form[$parent_key]['matrix_field_items'];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Do nothing here, because this method is not used.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    // Extract the values from $form_state->getValues().
    $key_exists = NULL;
    $raw_values = $form_state->getValue([$field_name, 'matrix_field_items']);
    $values = [];
    if (is_array($raw_values)) {
      foreach ($raw_values as $raw_value) {
        $values[] = [
          'field_id' => $raw_value['matrix_field_label']['field_id'],
          'matrix' => $raw_value['matrix_field_label']['matrix'],
          'field_value' => $raw_value['field_value'],
        ];
      }
    }
    $items->setValue($values);
    // Put delta mapping in $form_state, so that flagErrors() can use it.
    $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
    foreach ($items as $delta => $item) {
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->_weight);
    }
    if ($field_state === NULL) {
      $field_state = [];
    }
    static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
  }

  /**
   * {@inheritdoc}
   */
  public function flagErrors(
    FieldItemListInterface $items,
    ConstraintViolationListInterface $violations,
    array $form,
    FormStateInterface $form_state) {}


}
