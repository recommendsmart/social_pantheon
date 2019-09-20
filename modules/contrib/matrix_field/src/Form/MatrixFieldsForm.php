<?php

namespace Drupal\matrix_field\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\matrix_field\Entity\MatrixField;
use Drupal\matrix_field\Entity\MatrixFieldGroup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;

/**
 * Class MatrixFieldsForm.
 */
class MatrixFieldsForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $matrixStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fieldConfigStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $groupStorage;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * MatrixFieldsForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    RendererInterface $renderer
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->matrixStorage = $entity_type_manager->getStorage('matrix_field_matrix');
    $this->fieldConfigStorage = $entity_type_manager->getStorage('matrix_field');
    $this->groupStorage = $entity_type_manager->getStorage('matrix_field_group');
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrix_fields_form';
  }

  /**
   * Gets list of all field collections
   * @return array
   */
  public function getMatrices() {
    $matrixEntities = $this->matrixStorage->loadMultiple();
    $matrices = [];
    foreach ($matrixEntities as $entity) {
      $matrices[$entity->id()] = $entity->label();
    }
    return $matrices;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $page = $this->getRequest()->query->get('page') ?: 0;
    // TODO: make this variables configurable.
    $page_increment = 50;
    $extra_items = 5;
    $matrices = $this->getMatrices();
    $fields = $this->fieldConfigStorage->loadMultiple();
    pager_default_initialize(count($fields), $page_increment);
    // TODO: check if it possible to replace it by weight.
    uasort($fields, '_matrix_field_sort_fields');
    $groups = $this->groupStorage->loadMultiple();
    uasort($groups, '_matrix_field_sort_fields');
    // Sort fields with groups respecting.
    foreach ($groups as $group_key => $group) {
      $fields[$group_key] = $group;
      foreach ($fields as $key => $field) {
        if($field->get('parent') === $group_key) {
          unset($fields[$key]);
          $fields[] = $field;
        }
      }
    }

    $field_types = [
      'string' => $this->t('String'),
      'number' => $this->t('Number'),
      'boolean' => $this->t('Boolean'),
      'list' => $this->t('List'),
    ];
    $form['#attached']['library'][] = 'matrix_field/multiple_select';
    $form['#attached']['library'][] = 'matrix_field/matrix_fields';
    $form['fields'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => [
        $this->t('Field name'),
        $this->t('Machine name'),
        $this->t('Description'),
        $this->t('Field type'),
        $this->t('Unit'),
        $this->t('Weight'),
        $this->t('Delete'),
      ],
      '#empty' => $this->t('There are no fields yet'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'field-parent',
          'subgroup' => 'field-parent',
          'source' => 'field-id',
          'hidden' => FALSE,
        ],
      ],
      '#prefix' => '<div id="fields-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['matrix-field-table'],
      ],
    ];
    $form['fields']['#header'][] = $this->t('Matrices');

    $new_fields = $form_state->get('new_fields');
    if (is_array($new_fields)) {
      foreach ($new_fields as $new_field) {
        $fields[] = $new_field;
      }
      $fields += $new_fields;
    }
    $new_groups = $form_state->get('new_groups');
    if (is_array($new_groups)) {
      foreach ($new_groups as $new_group) {
        $fields[] = [
          'id' => $new_group,
          'group' => TRUE,
        ];
      }
    }
    $counter = 0;
    foreach ($fields as $field) {
      if ($counter < ($page * $page_increment) - $extra_items) {
        $counter++;
        continue;
      }
      if ($counter >= (($page + 1) * $page_increment) + $extra_items) {
        break;
      }
      $is_group = FALSE;
      if (is_object($field)) {
        $id = $field->get('weight') ?? preg_replace('/[^0-9,.]/', '', $field->id());
        $is_group = $field->getEntityTypeId() === 'matrix_field_group';
      } elseif (is_array($field)) {
        $id = $field['id'];
        $is_group = $field['group'];
      } else {
        $id = $field;
      }
      // Calculate initial depth.
      $depth = 0;
      if (!$is_group) {
        if (is_object($field)) {
          $depth = empty($field->get('parent')) ? 0 : 1;
        }
      }
      // Respect hierarchy.
      $indentation = [];
      if (isset($depth) && $depth > 0) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $depth,
        ];
      }
      // Don't rewrite exisiting fields with equal weights.
      if (isset($form['fields'][$id])) {
        while (isset($form['fields'][$id])) {
          $id--;
        }
      }

      $form['fields'][$id] = [
        '#weight' => $id,
        '#attributes' => [
          'class' => ['draggable'],
        ],
        'label' => [
          '#prefix' => !empty($indentation) ? $this->renderer->render($indentation) : '',
          '#type' => 'textfield',
          '#default_value' => is_object($field) ? $field->label() : NULL,
          '#size' => 25,
        ],
        'matrix_field' => [
          'id' => [
            '#type' => 'machine_name',
            '#default_value' => is_object($field) ? $field->id() : NULL,
            '#machine_name' => [
              'exists' => '\Drupal\matrix_field\Entity\MatrixField::load',
            ],
            '#disabled' => is_object($field) ? !$field->isNew() : FALSE,
            '#size' => 20,
            '#description' => NULL,
            '#attributes' => [
              'class' => ['field-id'],
            ],
          ],
          'parent' => [
            '#type' => 'hidden',
            // Yes, default_value on a hidden. It needs to be changeable by the
            // javascript.
            '#default_value' => is_object($field) ? $field->get('parent') : 0,
            '#attributes' => [
              'class' => ['field-parent'],
            ],
            '#weight' => 90,
          ],
        ],
        'description' => [
          '#title' => $this->t('Description'),
          '#type' => 'textarea',
          '#cols' => 30,
          '#rows' => 2,
          '#default_value' => is_object($field) ? $field->get('description') : NULL,
        ],
        'type' => $is_group ? [] : [
          '#type' => 'container',
          'field_type' => [
            '#type' => 'select',
            '#options' => $field_types,
            '#default_value' => is_object($field) ? $field->get('field_type') : NULL,
          ],
          'allowed_values' => [
            '#type' => 'textarea',
            '#cols' => 20,
            '#description' => $this->t('One value per row'),
            '#default_value' => is_object($field) && is_array($field->get('allowed_values')) ? implode(PHP_EOL, $field->get('allowed_values')) : NULL,
            '#states' => [
              'visible' => [
                'select[name="fields[' . $id . '][type][field_type]"]' => ['value' => 'list'],
              ],
              'required' => [
                'select[name="fields[' . $id . '][type][field_type]"]' => ['value' => 'list'],
              ],
            ],
          ],
        ],
        'unit' => [
          '#type' => 'textfield',
          '#default_value' => is_object($field) ? $field->get('unit') : NULL,
          '#size' => 10,
        ],
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight'),
          '#title_display' => 'invisible',
          '#default_value' => $id,
          '#delta' => 1000,
          // Classify the weight element for #tabledrag.
          '#attributes' => [
            'class' => [
              'table-sort-weight',
            ],
          ],
        ],
        'delete' => [
          '#markup' => is_object($field) ? Link::fromTextAndUrl(
            $this->t('Delete'),
            Url::fromRoute(
              'entity.' . $field->getEntityTypeId() . '.delete_form',
              [$field->getEntityTypeId() => $field->id()]
            )
          )->toString() : '<a href="#" class="delete-item-row" data-id="' . $id . '">' . $this->t('Delete') . '</a>',
        ],
      ];

      if (!$is_group) {
        $fc = is_object($field) ? $field->get('matrices') : [];
        // Handle programmatically generated fields with null-values.
        if (!$fc) {
          $fc = [];
        }
        $options_values = [];
        foreach ($matrices as $collection_id => $collection) {
          $options[$collection_id] = $collection;
          if (in_array($collection_id, $fc)) {
            $options_values[] =  $collection_id;
          }
        }
        $form['fields'][$id]['matrices'] = [
          '#type' => 'select',
          '#multiple' => TRUE,
          '#options' => $matrices,
          '#default_value' => $options_values,
          '#attributes' => [
            'class' => ['collections-select'],
          ],
        ];
      }
      $form['fields'][$id]['is_group'] = [
        '#type' => 'value',
        '#value' => $is_group,
      ];
      if ($counter < ($page * $page_increment) || $counter >= (($page + 1) * $page_increment)) {
        $form['fields'][$id]['#attributes']['class'][] = 'taxonomy-term-preview';
      }

      $counter++;
    }
    $form['add_more'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add more'),
      '#submit' => array('::addOne'),
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'fields-wrapper',
      ],
    ];
    $form['add_group'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add group'),
      '#submit' => array('::addGroup'),
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'fields-wrapper',
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ],
    ];
    $form['pager_pager'] = [
      '#type' => 'pager',
    ];
    // Attach this to mark extra items as gray.
    $form['#attached']['library'][] = 'taxonomy/drupal.taxonomy';
    return $form;
  }

  public function addGroup(array &$form, FormStateInterface $form_state) {
    $count = 0;
    foreach ($form['fields'] as $key => $value) {
      if (strpos($key, '#') === FALSE) {
        $count++;
      }
    }
    $new_groups = $form_state->get('new_groups');
    if ($new_groups === NULL) {
      $new_groups = [];
    }
    $new_groups[] = $count + 1;
    $form_state->set('new_groups', $new_groups);
    $form_state->setRebuild();
  }

  /**
   * Adds one more matrix field to table
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = 0;
    foreach ($form['fields'] as $key => $value) {
      if (strpos($key, '#') === FALSE) {
        $count++;
      }
    }
    $new_fields = $form_state->get('new_fields');
    if ($new_fields === NULL) {
      $new_fields = [];
    }
    $new_fields[] = $count + 1;
    $form_state->set('new_fields', $new_fields);
    $form_state->setRebuild();
  }

  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['fields'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * Helper function to flatten some extra encapsulation.
   * @param $field_name
   * @param $fields
   *
   * @return int
   */
  public function flattenParents($field_name, array $fields) {
    if (isset($fields[$field_name])) {
      if ($fields[$field_name]['is_group']) {
        return $field_name;
      }
      return $this->flattenParents($fields[$field_name]['parent'], $fields);
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $fields = $values['fields'];
    // Get map of all existing Matrix Fields.
    $field_map = [];
    foreach ($fields as $field) {
      $field_map[$field['matrix_field']['id']] = [
        'is_group' => $field['is_group'],
        'parent' => $field['matrix_field']['parent'],
      ];
    }
    foreach ($fields as $field) {
      $data = $field;
      $is_group = $data['is_group'];
      unset($data['is_group']);
      $data['id'] = $data['matrix_field']['id'];
      $data['parent'] = $data['matrix_field']['parent'];
      if ($data['parent']) {
        $data['parent'] = $this->flattenParents($data['parent'], $field_map);
      }
      unset($data['matrix_field']);
      if (!$is_group) {
        $data['allowed_values'] = explode(PHP_EOL, $data['type']['allowed_values']);
        $data['field_type'] = $data['type']['field_type'];
        unset($data['type']);
        $data['allowed_values'] = array_values($data['allowed_values']);
        $existing = $this->fieldConfigStorage->load($data['id']);
        if ($existing === NULL) {
          $entity = MatrixField::create($data);
          $entity->save();
        } else {
          foreach ($data as $key => $value) {
            $existing->set($key, $value);
          }
          $existing->save();
        }
      } else {
        unset($data['parent']);
        $existing = $this->groupStorage->load($data['id']);
        if ($existing === NULL) {
          $entity = MatrixFieldGroup::create($data);
          $entity->save();
        } else {
          foreach ($data as $key => $value) {
            $existing->set($key, $value);
          }
          $existing->save();
        }
      }
    }
  }
}
