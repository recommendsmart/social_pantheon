<?php

namespace Drupal\agerp_core_basic\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for the Individual entity.
 */
class PartyForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $party = $this->entity;

    $status = $party->save();

    $t_args = ['%name' => $party->label(), 'link' => $party->url()];

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The party %name has been updated.', $t_args));
      if ($party->access('view')) {
        $form_state->setRedirect('entity.agerp_core_party.canonical', ['agerp_core_party' => $party->id()]);
      }
      else {
        $form_state->setRedirect('entity.agerp_core_party.collection');
      }
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message($this->t('The party %name has been added.', $t_args));
      \Drupal::logger('agerp_core_party')->notice('Added party %name.', $t_args);
      $form_state->setRedirect('entity.agerp_core_party.collection');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save @party_type', [
      '@party_type' => $this->entity->get('type')->entity->label(),
    ]);
    return $actions;
  }

}
