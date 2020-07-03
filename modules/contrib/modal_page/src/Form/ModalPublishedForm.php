<?php

namespace Drupal\modal_page\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class: ModalPublishedForm.
 */
class ModalPublishedForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->getEntity();

    if ($entity->published->value) {
      return $this->t('Are you sure you want to unpublish entity %title?', ['%title' => $this->entity->label()]);
    }

    return $this->t('Are you sure you want to publish entity %title?', ['%title' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action can be fixed in the future.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('modal_page.default');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Comfirm');
  }

  /**
   * {@inheritdoc}
   *
   * Pubished the entity and log the event. logger() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();

    if ($entity->published->value) {
      $entity->published->value = FALSE;
    }
    else {
      $entity->published->value = TRUE;
    }

    $entity->save();

    $form_state->setRedirect('modal_page.default');
  }

}
