<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Financial Document edit forms.
 *
 * @ingroup dfinance
 */
class FinancialDocForm extends ContentEntityForm {

  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if ($this->getRouteMatch()->getRawParameter('finance_organisation') != NULL) {
      unset($form['organisation']);
    }

    return $form;
  }

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

    $this->messenger()->addMessage($this->t('%action the %label Financial Document', [
      '%action' => $status == SAVED_NEW ? 'Created' : 'Saved',
      '%label' => $entity->label(),
    ]));

    $form_state->setRedirect('entity.financial_doc.canonical', ['financial_doc' => $entity->id()]);
  }

}
