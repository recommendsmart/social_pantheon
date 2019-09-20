<?php

namespace Drupal\matrix_field\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MatrixForm.
 */
class MatrixForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $matrix = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $matrix->label(),
      '#description' => $this->t("Label for the Matrix."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $matrix->id(),
      '#machine_name' => [
        'exists' => '\Drupal\matrix_field\Entity\Matrix::load',
      ],
      '#disabled' => !$matrix->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $matrix = $this->entity;
    $status = $matrix->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Matrix.', [
          '%label' => $matrix->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Matrix.', [
          '%label' => $matrix->label(),
        ]));
    }
    $form_state->setRedirectUrl($matrix->toUrl('collection'));
  }

}
