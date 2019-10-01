<?php

namespace Drupal\nbox\Entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NboxTypeForm.
 */
class NboxTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $nbox_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $nbox_type->label(),
      '#description' => $this->t("Label for the nbox type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $nbox_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\nbox\Entity\NboxType::load',
      ],
      '#disabled' => !$nbox_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $nbox_type = $this->entity;
    $status = $nbox_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Nbox type.', [
          '%label' => $nbox_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Nbox type.', [
          '%label' => $nbox_type->label(),
        ]));

    }
    $form_state->setRedirectUrl($nbox_type->toUrl('collection'));
  }

}
