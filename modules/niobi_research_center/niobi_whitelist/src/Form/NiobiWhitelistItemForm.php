<?php

namespace Drupal\niobi_whitelist\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Whitelist Item edit forms.
 *
 * @ingroup niobi_whitelist
 */
class NiobiWhitelistItemForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\niobi_whitelist\Entity\NiobiWhitelistItem */
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

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Whitelist Item.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Whitelist Item.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.niobi_whitelist_item.canonical', ['niobi_whitelist_item' => $entity->id()]);
  }

}
