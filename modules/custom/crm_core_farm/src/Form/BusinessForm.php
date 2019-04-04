<?php

namespace Drupal\crm_core_farm\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BusinessForm.
 *
 * Provides a form for the Business entity.
 *
 * @package Drupal\crm_core_farm\Form
 */
class BusinessForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $business = $this->entity;

    $status = $business->save();

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The business %name has been updated.', ['%name' => $business->label()]));
      if ($business->access('view')) {
        $form_state->setRedirect('entity.crm_core_business.canonical', ['crm_core_business' => $business->id()]);
      }
      else {
        $form_state->setRedirect('entity.crm_core_business.collection');
      }
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message($this->t('The business %name has been added.', ['%name' => $business->label()]));
      \Drupal::logger('crm_core_business')->notice('Added business %name.', ['%name' => $business->label()]);
      $form_state->setRedirect('entity.crm_core_business.collection');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save @business_type', [
      '@business_type' => $this->entity->get('type')->entity->label(),
    ]);
    return $actions;
  }

}
