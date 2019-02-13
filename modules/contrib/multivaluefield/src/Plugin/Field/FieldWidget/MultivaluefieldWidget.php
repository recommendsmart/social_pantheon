<?php

namespace Drupal\multivaluefield\Plugin\Field\FieldWidget;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldConfigBase;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\Plugin\DataType\FieldItem;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'image_image' widget.
 *
 * @FieldWidget(
 *   id = "multivaluefield_widget",
 *   label = @Translation("Multivaluefield"),
 *   field_types = {
 *     "multivaluefield"
 *   }
 * )
 */
class MultivaluefieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'label_type' => 'placeholder',
        'field_inline' => 0,
      ) + parent::defaultSettings();
  }

  /**
   * Get label display types list.
   */
  public static function getFieldLabelDisplayTypesList() {
    return [
      'placeholder' => 'Placeholder',
      'label' => 'Label',
      'both' => 'Both',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $label_types = self::getFieldLabelDisplayTypesList();
    $element['label_type'] = array(
      '#title' => t('Field label type'),
      '#type' => 'select',
      '#options' => $label_types,
      '#default_value' => $this->getSetting('label_type'),
      '#weight' => 15,
    );
    $element['field_inline'] = array(
      '#title' => t('Display fields as inline'),
      '#description' => t('Display fields as display inline-flex'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('field_inline'),
      '#weight' => 15,
    );


    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $fields_list = $this->getFieldSetting('fields');
    $label_types = self::getFieldLabelDisplayTypesList();
    $label_type = $this->getSetting('label_type');
    $field_inline = $this->getSetting('field_inline') ? 'Inline' : '';

    $text = "Fields : ";
    $count = 0;
    foreach ($fields_list as $key => $field) {
      if ($count) {
        $text .= ", ";
      }
      $text .= $field['name'];
      $count++;
    }
    $summary[] = t('Number of fields @count. (@list)', [
      '@count' => $this->getFieldSetting('fields_count'),
      '@list' => $text
    ]);
    $summary[] = t('Form label type : @type. @inline', [
      '@type' => $label_types[$label_type],
      '@inline' => $field_inline,
    ]);

    return $summary;
  }


  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $item_values = $items->getValue();

    $field_inline = $this->getSetting('field_inline');

    $elements = parent::formMultipleElements($items, $form, $form_state);
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()
      ->getCardinality();

    //Add lib
    if ($field_inline) {
      $elements['#attached']['library'][] = 'multivaluefield/inlinefields.style';
    }

    //kint($elements);
    foreach ($item_values as $key => $item_value) {
      $item_data = NULL;
      if (!empty($item_value['data'])) {
        $item_data = $item_value['data'];
      }
      elseif (is_array($item_value)) {
        $item_data = $item_value;
      }

      $ei = "$key"; //Element Index
      if ($item_data) {
        foreach ($item_data as $item_key => $item_v) {
          $ii = "$item_key"; //Item Index
          if (isset($elements[$ei][$ii])) {

            $type = $elements[$ei][$ii]['#type'];
            $default_value = $item_v;
            if ($type === "entity_autocomplete") {
              try {
                $target_type = $elements[$ei][$ii]['#target_type'];
                $default_value = \Drupal::entityTypeManager()
                  ->getStorage($target_type)
                  ->load($item_v);;
              }
              catch (\Exception $e) {
              }
            }

            $elements[$ei][$ii]['#default_value'] = $default_value;
          }
        }
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    //$element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element = array();
    $settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();


    $fields_count = $field_settings['fields_count'];
    $fields_list = $field_settings['fields'];
    $index_field_name = $field_settings['index_field_name'];
    $index_field_hide = $field_settings['index_field_hide'];
    $index_field_pos = $field_settings['index_field_pos'];


    $label_type = $this->getSetting('label_type');
    $label_type_title = ($label_type == 'label' || $label_type == 'both');
    $label_type_plhol = ($label_type == 'placeholder' || $label_type == 'both');


    $count = 0;
    foreach ($fields_list as $key => $field) {


      //Add index field (Index field holder)
      if ($index_field_pos == $key) {
        $element['index'] = [];
      }

      $field_name = t($field['name']);
      $field_type = $field['type'];
      $field_conf = $field['conf'];


      if (strstr($field_type, 'basicfield_') !== FALSE) {
        $element[$count] = array(
          '#type' => 'textfield',
          '#maxlength' => 1024,
          '#default_value' => '',
        );
        if ($field_type === 'basicfield_int') {
          $element[$count]['#type'] = 'number';
        }
        elseif ($field_type === 'basicfield_bool') {
          $element[$count]['#type'] = 'checkbox';
          $element[$count]['#title'] = $field_name;
        }
        elseif ($field_type === 'basicfield_list' || $field_type === 'basicfield_radios') {
          //Multiple list selection
          if ($field_type === 'basicfield_radios') {
            $element[$count]['#type'] = 'radios';
          }
          elseif (FALSE || $field_type === 'basicfield_checks') {
            //Tempory disabled, because multiple values not handeleing .
            $element[$count]['#type'] = 'checkboxes';
          }
          else {
            $element[$count]['#type'] = 'select';
            $element[$count]['#empty_value'] = '';
          }


          //Get options list
          $options_temp = explode("\n", $field_conf);
          $options = [];
          foreach ($options_temp as $items) {
            if (strstr($items, '|') === FALSE) {
              $options[$items] = $items;
            }
            else {
              $items_data = explode("|", $items);
              $options[$items_data[0]] = $items_data[1];
            }
          }
          $element[$count]['#options'] = $options;
        }

      }
      else {
        //@TODO : Test with all type of Entities.
        $element[$count] = array(
          '#type' => 'entity_autocomplete',
          '#target_type' => $field_type,
          '#default_value' => '',
        );
      }

      //Add title to tooltip text
      $element[$count]['#attributes']['title'] = $field_name;
      //Set field title and/or placeholder.
      if ($label_type_title) {
        $element[$count]['#title'] = $field_name;
      }
      if ($label_type_plhol) {
        $element[$count]['#placeholder'] = $field_name;
      }

      $count++;
    }

    //Index field configuration.
    //Fill index field config.
    $element['index'] = array(
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('mvfindex'),
    );
    if (!$index_field_name) {
      //IF index field label is empty.
      $index_field_name = "Index field";
    }
    if ($label_type_title) {
      $element['index']['#title'] = t($index_field_name);
    }
    if ($label_type_plhol) {
      $element['index']['#attributes']['placeholder'] = t($index_field_name);
    }
    if ($index_field_hide) {
      $element['index']['#type'] = 'hidden';
    }
    //End of Index field configuration.

    return $element;
  }
}
