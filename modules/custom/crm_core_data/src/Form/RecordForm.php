<?php

namespace Drupal\crm_core_data\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RecordForm.
 *
 * Provides a form for the Record entity.
 *
 * @package Drupal\crm_core_data\Form
 */
class RecordForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $record = $this->entity;

    $status = $record->save();

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The record %name has been updated.', ['%name' => $record->label()]));
      if ($record->access('view')) {
        $form_state->setRedirect('entity.crm_core_record.canonical', ['crm_core_record' => $record->id()]);
      }
      else {
        $form_state->setRedirect('entity.crm_core_record.collection');
      }
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message($this->t('The record %name has been added.', ['%name' => $record->label()]));
      \Drupal::logger('crm_core_record')->notice('Added record %name.', ['%name' => $record->label()]);
      $form_state->setRedirect('entity.crm_core_record.collection');
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
