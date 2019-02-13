<?php

namespace Drupal\multivaluefield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Plugin implementation of the 'multivaluefield' field formatter.
 *
 * @FieldFormatter(
 *   id = "multivaluefield_formatter",
 *   label = @Translation("Multivaluefield"),
 *   field_types = {
 *     "multivaluefield"
 *   }
 * )
 */
class MultivaluefieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {


  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'display_type' => 'fields',
        'display_field_label' => 1,
        'display_selectable' => 0,
        'display_entity' => 1,
        'index_hide' => 0,
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $display_types = self::getDataDisplayTypesList();
    $elements['display_type'] = [
      '#title' => t('Display type'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('display_type'),
      '#options' => $display_types,
      '#description' => 'The description',
    ];

    $elements['display_field_label'] = [
      '#title' => t('Display field label'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('display_field_label'),
      '#description' => 'Label for "Fields", Header for "Table"',
    ];

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()
      ->getCardinality();
    if ($cardinality != 0) {
      $elements['display_selectable'] = [
        '#title' => t('Selectable'),
        '#type' => 'checkbox',
        '#default_value' => $this->getSetting('display_selectable'),
        '#description' => 'Add selectable option to the field',
      ];

      //Hide field conditional
      $elements['display_selectable_style'] = array(
        '#title' => t('Apply default CSS style'),
        '#type' => 'checkbox',
        '#default_value' => $this->getSetting('display_selectable_style'),
        '#states' => array(
          'visible' => array(
            ':input[name$="[settings_edit_form][settings][display_selectable]"]' => array('checked' => TRUE),
          ),
        ),
      );
    }

    //@TODO : Add mode
    $elements['display_entity'] = [
      '#title' => t('Entity display'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('display_entity'),
      '#options' => [
        1 => "Entity ID (Default)",
        2 => "Entity Label",
        3 => "Entity - Object (For custom templates)",
        4 => "Entity - Array (For custom templates)",
        0 => "Hidden",
      ],
      '#description' => 'For the best performance, Use the "Entity ID" mode',
    ];

    //Hide index field from display
    $elements['index_hide'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t("Hide index field"),
      '#default_value' => $this->getSetting('index_hide'),
    );

    return $elements;
  }

  /**
   * Get field display types list.
   */
  public static function getDataDisplayTypesList() {
    return [
      'fields' => "Fields",
      'table' => "Table",
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $display_type = $this->getSetting('display_type');
    $display_field_label = $this->getSetting('display_field_label');
    $display_types = self::getDataDisplayTypesList();
    $summary[] = t('Data display type : @style', array('@style' => $display_types[$display_type]));
    if ($display_field_label) {
      $summary[] = t('+ Field label');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();


    $field_settings = $items->getSettings();
    $fields_list = $field_settings['fields'];
    $index_field_hide = $field_settings['index_field_hide'];
    $index_field_pos = $field_settings['index_field_pos'];


    $values = $items->getValue();
    $display_type = $this->getSetting('display_type');
    $display_field_label = $this->getSetting('display_field_label');
    $display_selectable = $this->getSetting('display_selectable');
    $display_selectable_style = $this->getSetting('display_selectable_style');
    $display_entity = $this->getSetting('display_entity');
    $index_hide = $this->getSetting('index_hide');
    //Hide index field if globally hidden
    if ($index_field_hide) {
      $index_hide = $index_field_hide;
    }

    //Settings for theme template.
    $theme_settings = [];
    $theme_settings['#field_name'] = $items->getName();
    $theme_settings['#entity_type'] = $items->getEntity()->getEntityTypeId();
    $theme_settings['#bundle'] = $items->getEntity()->bundle();

    //Add library if necessary.
    if ($display_selectable) {
      //Javascript library.
      $elements['#attached']['library'][] = 'multivaluefield/selectable';
      if ($display_selectable_style) {
        //CSS library.
        $elements['#attached']['library'][] = 'multivaluefield/selectable.style';
      }
    }

    //Set Data
    $header = [];
    $entities = [];
    $datas = [];


    //Process header
    foreach ($fields_list as $key => $field) {

      //Add Index field name to header
      if (!$index_hide && $index_field_pos == $key) {
        $header['index'] = $field_settings['index_field_name'];
      }

      //Add field title to Header
      $header[$key] = $field['name'];

      if (strstr($field['type'], 'basicfield_') === FALSE) {
        //Not basicfield = Entity
        $entities[$key] = $field['type'];

        //Remove Header if display mode == Hidden.
        if ($display_entity == 0) {
          unset($header[$key]);
        }
      }
    }
    //Add Index field name if not already done
    if ($index_hide && !isset($header['index'])) {
      $header['index'] = $field_settings['index_field_name'];
    }

    //Process datas
    foreach ($values as $delta => $data) {
      if (isset($data['index'])) {

        $data_show = [];


        foreach ($fields_list as $key => $field) {
          //Add Index field value to data
          if (!$index_hide && $index_field_pos == $key) {
            $data_show['index'] = $data['index'];
          }
          $data_show[$key] = $data[$key];
        }

        //Add Index field name if not already done
        if ($index_hide && !isset($data_show['index'])) {
          $data_show['index'] = $data['index'];
        }

        $datas[$delta] = $data_show;
      }
    }


    //Render Entities Names / Objects
    if ($display_entity == 2 || $display_entity == 3 || $display_entity == 4) {
      //Mode : Entity Label
      foreach ($datas as $delta => $data) {
        foreach ($entities as $key => $entity_type) {
          $entity_id = $datas[$delta][$key];
          $datas[$delta][$key] = "";
          if ($entity_id) {
            try {
              $entity_v = \Drupal::entityTypeManager()
                ->getStorage($entity_type)
                ->load($entity_id);
              if ($display_entity == 3) {
                $datas[$delta][$key] = $entity_v;

              }
              elseif ($display_entity == 4) {
                $datas[$delta][$key] = [
                  "id" => $entity_v->id(),
                  "uuid" => $entity_v->uuid(),
                  "label" => $entity_v->label(),
                  "bundle" => $entity_v->bundle(),
                  "type" => $entity_v->getEntityTypeId(),
                ];
              }
              else {
                // $display_entity == 2
                $datas[$delta][$key] = $entity_v->label();
              }
            }
            catch (\Exception $e) {
            }
          }
        }
      }
    }
    elseif ($display_entity == 1) {
      //Default Mode : Entity ID
    }
    elseif ($display_entity == 0) {
      //Mode Hidden : Remove Entities
      foreach ($datas as $delta => $data) {
        foreach ($entities as $key => $entity_type) {
          unset($datas[$delta][$key]);
        }
      }
    }

    //Wrapper Attributes.
    $attributes = ['class' => ['multivaluefield']];


    if ($display_type == 'table') {
      // Display type : Table.

      $element = array(
        '#theme' => 'table',
        '#rows' => $datas,
        '#attributes' => $attributes,
      );
      //Add header.
      if ($display_field_label) {
        $element['#header'] = $header;
      }
      $elements += $element;
    }
    elseif ($display_type == 'fields') {
      // Display type : Fields.
      $element = [
        '#theme' => 'multivaluefield',
        '#rows' => $datas,
        '#attributes' => $attributes,
        '#settings' => $theme_settings,
      ];
      //Add header
      if ($display_field_label) {
        $element['#header'] = $header;
      }
      $elements += $element;
    }

    return $elements;
  }
}