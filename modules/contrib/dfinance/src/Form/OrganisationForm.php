<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Organisation edit forms.
 *
 * @ingroup dfinance
 */
class OrganisationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\dfinance\Entity\Organisation */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    $this->messenger()->addMessage($this->t('%action the %label Organisation', [
      '%action' => $status == SAVED_NEW ? 'Created' : 'Saved',
      '%label' => $entity->label(),
    ]));

    $form_state->setRedirect('entity.finance_organisation.canonical', ['finance_organisation' => $entity->id()]);
  }

}
