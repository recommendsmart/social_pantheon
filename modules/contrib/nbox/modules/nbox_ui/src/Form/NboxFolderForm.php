<?php

namespace Drupal\nbox_ui\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Nbox folder edit forms.
 *
 * @ingroup nbox_folders
 */
class NboxFolderForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\nbox_folders\Entity\NboxFolder */
    $form = parent::buildForm($form, $form_state);

    $form['uid']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Nbox folder.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Nbox folder.', [
          '%label' => $entity->label(),
        ]));

    }
    $form_state->setRedirect('view.nbox_mailbox.page_1', [
      'arg_0' => 'inbox',
    ]);
  }

}
