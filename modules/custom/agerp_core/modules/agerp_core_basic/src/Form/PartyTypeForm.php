<?php

namespace Drupal\agerp_core_basic\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for edit party types.
 */
class PartyTypeForm extends EntityForm {

  /**
   * Default primary fields.
   *
   * TODO: Add option back to alter primary fields.
   *
   * @var array
   */
  protected $defaultPrimaryFields = ['email', 'address', 'phone'];

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var \Drupal\agerp_core_basic\Entity\PartyType $type */
    $type = $this->entity;

    $form['name'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->name,
      '#description' => $this->t('The human-readable name of this party type. It is recommended that this name begin with a capital letter and contain only letters, numbers, and spaces. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 32,
    ];

    $form['type'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => 'Drupal\agerp_core_basic\Entity\PartyType::load',
        'source' => ['name'],
      ],
      '#description' => $this->t('A unique machine-readable name for this party type. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->description,
      '#description' => $this->t('Describe this party type.'),
    ];

    // Primary fields section.
    $form['primary_fields_container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Primary Fields'),
      '#description' => $this->t('Primary fields are used to tell other modules what fields to use for common communications tasks such as sending an email, addressing an envelope, etc. Use the fields below to indicate the primary fields for this party type.'),
    ];

    $options = [];
    if ($type) {
      /** @var \Drupal\Core\Entity\EntityFieldManager $field_manager */
      $field_manager = \Drupal::service('entity_field.manager');
      $instances = $field_manager->getFieldDefinitions('agerp_core_party', $type->id());
      foreach ($instances as $instance) {
        $options[$instance->getName()] = $instance->getLabel();
      }
    }
    foreach ($this->defaultPrimaryFields as $primary_field) {
      $form['primary_fields_container'][$primary_field] = [
        '#type' => 'select',
        '#title' => $this->t('Primary @field field', ['@field' => $primary_field]),
        '#default_value' => empty($type->primary_fields[$primary_field]) ? '' : $type->primary_fields[$primary_field],
        '#empty_value' => '',
        '#empty_option' => $this->t('--Please Select--'),
        '#options' => $options,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save party type');
    $actions['delete']['#title'] = $this->t('Delete party type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $primary_fields = [];
    foreach ($this->defaultPrimaryFields as $field) {
      $primary_fields[$field] = $form_state->getValue($field);
    }
    $this->entity->set('primary_fields', $primary_fields);

    $status = $this->entity->save();

    $arguments = [
      '%name' => $this->entity->label(),
      'link' => Url::fromRoute('entity.agerp_core_party_type.collection'),
    ];

    if ($status == SAVED_UPDATED) {
      $this->messenger()
        ->addMessage($this->t('The party type %name has been updated.', $arguments));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()
        ->addMessage($this->t('The party type %name has been added.', $arguments));
      $this->logger('agerp_core_party')
        ->notice('Added party type %name.', $arguments);
    }

    $form_state->setRedirect('entity.agerp_core_party_type.collection');
  }

}
