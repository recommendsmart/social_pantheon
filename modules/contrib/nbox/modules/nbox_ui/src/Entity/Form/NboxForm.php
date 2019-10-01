<?php

namespace Drupal\nbox_ui\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Nbox edit forms.
 *
 * @ingroup nbox
 */
class NboxForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\nbox\Entity\Nbox */
    $entity = $this->getEntity();

    $form = parent::buildForm($form, $form_state);

    if ($entity->isReply()) {
      $form['actions']['submit']['#submit'][] = '::postSaveReply';
    }
    return $form;
  }

  /**
   * Custom submit for replies.
   */
  public function postSaveReply(array $form, FormStateInterface $form_state) {
    $messenger = $this->messenger();
    $messenger->deleteAll();
    $entity = $this->getEntity();
    if ($entity->forward) {
      $messenger->addMessage('Your message has been forwarded.');
    }
    else {
      $messenger->addMessage('Your reply has been sent.');
    }
    $form_state->setRedirect('view.nbox_mailbox.page_1', [
      'arg_0' => 'inbox',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Nbox.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Nbox', [
          '%label' => $entity->label(),
        ]));

    }
    $form_state->setRedirect('entity.nbox.canonical', ['nbox' => $entity->id()]);
  }

}
