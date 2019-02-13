<?php

namespace Drupal\multivaluefield\Plugin\Field\FieldType;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;


/**
 * Plugin implementation of the 'multivaluefield' field type.
 *
 * @FieldType(
 *   id = "multivaluefield",
 *   label = @Translation("Multivalue field"),
 *   description = @Translation("This field stores the ID of an image file as an integer value."),
 *   category = @Translation("General"),
 *   default_widget = "multivaluefield_widget",
 *   default_formatter = "multivaluefield_formatter"
 * )
 */
class MultivaluefieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
        'fields_count' => 2,
        'fields' => array(
          '0' => array(
            'name' => 'Key',
            'type' => 'basicfield_text',
            'conf' => '',
          ),
          '1' => array(
            'name' => 'Value',
            'type' => 'basicfield_text',
            'conf' => '',
          ),
        ),
        'index_field_name' => 'Index Field',
        'index_field_hide' => 0,
        'index_field_pos' => 0,
      ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = array(
        'default_value' => [],
      ) + parent::defaultFieldSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'index' => array(
          'description' => 'Multi value field indexable field, A title.',
          'type' => 'varchar',
          'length' => 255,
        ),
        'data' => array(
          'description' => 'JSON array for the multi fields data.',
          'type' => 'blob',
          'size' => 'big',
        ),
      ),
      'indexes' => array(
        'index' => array('index'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    //Index field
    $properties['index'] = DataDefinition::create('string')
      ->setLabel(t('Index'));

    //Data (Map) field
    $properties['data'] = DataDefinition::create('any')
      ->setLabel(t('Data Json Array'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();

    $settings = $this->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSettings();

    $element['fields_count'] = array(
      '#type' => 'number',
      '#title' => $this->t('Number of fields'),
      '#default_value' => $settings['fields_count'],
      '#min' => 1,
    );
    $element['index_field_hide'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t("Hide index field"),
      '#default_value' => $settings['index_field_hide'],
      '#description' => $this->t("Hide index field from form and display (Globally)."),
    );
    $element['index_field_pos'] = array(
      '#type' => 'number',
      '#title' => $this->t('Index field position'),
      '#default_value' => $settings['index_field_pos'],
      '#min' => 0,
      '#max' => $settings['fields_count'] + 1,
      '#states' => array(
        'invisible' => array(
          ':input[id="edit-settings-index-field-hide"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['index_field_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Index field Title"),
      '#default_value' => $settings['index_field_name'],
      '#description' => $this->t("Set the name of the 'Index' field."),
      '#states' => array(
        'invisible' => array(
          ':input[id="edit-settings-index-field-hide"]' => array('checked' => TRUE),
        ),
      ),
    );


    $settings_fields = $this->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSettings();
    $fields_count = $settings_fields['fields_count'];

    $field_type_groups = [
      'basic' => "" . $this->t('Basic field'),
      'content' => "" . $this->t('Content Entity'),
      'configuration' => "" . $this->t('Configuration Entity'),
    ];

    //Field types
    $field_type_options = array();
    $field_type_options['basic']['basicfield_text'] = 'Text';
    $field_type_options['basic']['basicfield_int'] = 'Integer';
    $field_type_options['basic']['basicfield_bool'] = 'Checkbox';
    $field_type_options['basic']['basicfield_list'] = 'Selections list';
    $field_type_options['basic']['basicfield_radios'] = 'Selections radio buttons';
    //$field_type_options['basic']['basicfield_checks'] = 'Selections checkboxes'; //Because multiple values are not handeling

    $itmes_list = \Drupal::entityTypeManager()->getDefinitions();


    foreach ($itmes_list as $itme_name => $item_object) {
      $category = $item_object->getGroup();
      $field_type_options[$category][$itme_name] = $item_object->getLabel();
    }

    //Rearange option groups
    foreach ($field_type_groups as $key => $name) {
      if (isset($field_type_options[$key]) && $name) {
        $field_type_options[$name] = $field_type_options[$key];
        unset($field_type_options[$key]);
      }
    }

    $element['fields'] = array(
      '#type' => 'details',
      '#title' => 'Fields',
      '#open' => TRUE,
    );

    for ($i = 0; $i < $fields_count; $i++) {
      $di = "$i"; //Data index
      $element['fields'][$di] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Field') . " - " . $i,
      );
      $element['fields'][$di]['name'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Field Name'),
        '#required' => TRUE,
        '#default_value' => empty($settings['fields'][$di]['name']) ? 'Untitled' : $settings['fields'][$di]['name'],
      );
      $element['fields'][$di]['type'] = array(
        '#type' => 'select',
        '#title' => $this->t('Field Type'),
        '#options' => $field_type_options,
        '#required' => TRUE,
        '#default_value' => empty($settings['fields'][$di]['type']) ? '' : $settings['fields'][$di]['type'],
      );

      //Dynamic / Conditional field
      $field_id = "edit-settings-fields-$di-type";//Ex : edit-settings-fields-1-type
      $element['fields'][$di]['conf'] = array(
        '#title' => t('Field configuration'),
        '#description' => t('Add values list, One per line.'),
        '#type' => 'textarea',
        '#default_value' => empty($settings['fields'][$di]['conf']) ? '' : $settings['fields'][$di]['conf'],
        '#states' => array(
          'visible' => array(
            [':input[id="' . $field_id . '"]' => array('value' => 'basicfield_list')],
            [':input[id="' . $field_id . '"]' => array('value' => 'basicfield_radios')],

            //':input[id="' . $field_id . '"]' => array('value' => 'basicfield_radios'),
            //':input[id="' . $field_id . '"]' => array('value' => 'basicfield_checks'),
          ),
        ),
      );
    }

    return $element;
  }

  /**
   * Ajax method of storageSettingsForm.
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */

  function storageSettingsFormAjax(array &$form, FormStateInterface $form_state) {

    //$form['testfield']['#title'] = "YES";
    $form['fieldsset']['#type'] = "hidden";
    return $form['fieldsset'];
    //return $form['testfield'];

    //error_log(print_r($form_state->getValues(), TRUE));
    //$form['fields']['0']['config']['#type'] = "textfield";
    //return $form['fields']['0']['config'];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    $element = parent::fieldSettingsForm($form, $form_state);
    return $element;
  }

  /**
   * {@inheritdoc}
   */

  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    //@TODO : Return random value according to the settings.
    $settings = $field_definition->getSettings();
    $values = [
      'index' => "Random Index $random",
      '0' => "Key random $random",
      '1' => "Value random $random",
    ];
    return $values;
  }


  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $values = $this->getValue();

    $flg_data = FALSE;
    foreach ($values as $value) {
      if ($value) {
        $flg_data = TRUE;
        break;
      }
    }
    if ($flg_data) {
      $this->data = Json::encode($values);
    }
    else {
      $this->data = NULL;
    }

    parent::preSave();
  }


  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {

    //Delete Value if empty
    $flg_data = FALSE;
    foreach ($values as $value) {
      if ($value) {
        $flg_data = TRUE;
        break;
      }
    }
    if (!$flg_data) {
      $values = NULL;
    }

    parent::setValue($values, $notify);
  }


  /**
   * {@inheritdoc}
   */
  public function getValue($field = NULL) {
    $value = parent::getValue();
    if (is_string($value['data'])) {
      $value = Json::decode($value['data']);
    }

    //Get selected field
    if ($field) {
      if ($field == 'index') {
        //Return Index field
        $value = $value['index'];
      }
      elseif ($field == 'data') {
        //Return data fields including index field
        $value['data']['index'] = $value['index'];
        $value = $value['data'];
      }
      elseif (isset($value[$field])) {
        //Looking for a custom field.
        $value = $value[$field];
      }
      else {
        //Looking for undefined field.
        $value = NULL;
      }
    }
    return $value;
  }
}
