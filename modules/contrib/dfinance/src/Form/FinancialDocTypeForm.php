<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FinancialDocTypeForm.
 */
class FinancialDocTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\dfinance\Entity\FinancialDocTypeInterface $financial_doc_type */
    $financial_doc_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $financial_doc_type->label(),
      '#description' => $this->t("Label for the Financial Document type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $financial_doc_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\dfinance\Entity\FinancialDocType::load',
      ],
      '#disabled' => !$financial_doc_type->isNew(),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $financial_doc_type->getDescription(),
      '#description' => t('This text will be displayed on the <em>Add Financial Document</em> page.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $financial_doc_type = $this->entity;
    $status = $financial_doc_type->save();

    $this->messenger()->addMessage($this->t('%action the %label Financial Document Type', [
      '%action' => $status == SAVED_NEW ? 'Created' : 'Saved',
      '%label' => $financial_doc_type->label(),
    ]));

    $form_state->setRedirectUrl($financial_doc_type->toUrl('collection'));
  }

}
