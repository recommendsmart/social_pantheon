<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Supplier edit forms.
 *
 * @ingroup dfinance
 */
class SupplierForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision') && $form_state->getValue('revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    $this->messenger()->addMessage($this->t('%action the %label Supplier', [
      '%action' => $status == SAVED_NEW ? 'Created' : 'Saved',
      '%label' => $entity->label(),
    ]));

    $form_state->setRedirect('entity.finance_supplier.canonical', ['finance_supplier' => $entity->id()]);
  }

}
