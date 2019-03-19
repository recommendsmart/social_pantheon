<?php

/**
 * @file
 * Contains \Drupal\redhen_liability\Form\LiabilityTypeForm.
 */

namespace Drupal\redhen_liability\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LiabilityTypeForm.
 *
 * @package Drupal\redhen_liability\Form
 */
class LiabilityTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $redhen_liability_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $redhen_liability_type->label(),
      '#description' => $this->t("Label for the liability type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $redhen_liability_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\redhen_liability\Entity\LiabilityType::load',
      ),
      '#disabled' => !$redhen_liability_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $redhen_liability_type = $this->entity;
    $status = $redhen_liability_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Liability type.', [
          '%label' => $redhen_liability_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Liability type.', [
          '%label' => $redhen_liability_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($redhen_liability_type->urlInfo('collection'));
  }

}
