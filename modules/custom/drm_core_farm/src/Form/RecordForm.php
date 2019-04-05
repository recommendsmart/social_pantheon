<?php

namespace Drupal\drm_core_farm\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for the Record entity.
 */
class RecordForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $record = $this->entity;

    $status = $record->save();

    $t_args = array('%name' => $record->label(), 'link' => $record->url());

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The record %name has been updated.', $t_args));
      if ($record->access('view')) {
        $form_state->setRedirect('entity.drm_core_record.canonical', ['drm_core_record' => $record->id()]);
      }
      else {
        $form_state->setRedirect('entity.drm_core_record.collection');
      }
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message($this->t('The record %name has been added.', $t_args));
      \Drupal::logger('drm_core_record')->notice('Added record %name.', $t_args);
      $form_state->setRedirect('entity.drm_core_record.collection');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save @record_type', [
      '@record_type' => $this->entity->get('type')->entity->label(),
    ]);
    return $actions;
  }

}
