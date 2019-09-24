<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AccountCodeEntityTypeForm.
 */
class AccountCodeTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $financial_account_code_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $financial_account_code_type->label(),
      '#description' => $this->t("Label for the Account Code type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $financial_account_code_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\dfinance\Entity\AccountCodeType::load',
      ],
      '#disabled' => !$financial_account_code_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $financial_account_code_type = $this->entity;
    $status = $financial_account_code_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Account Code type.', [
          '%label' => $financial_account_code_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Account Code type.', [
          '%label' => $financial_account_code_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($financial_account_code_type->toUrl('collection'));
  }

}
