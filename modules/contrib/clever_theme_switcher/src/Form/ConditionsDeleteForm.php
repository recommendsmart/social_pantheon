<?php

namespace Drupal\clever_theme_switcher\Form;

use Drupal\clever_theme_switcher\Entity\Cts;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting an condition.
 */
class ConditionsDeleteForm extends ConfirmFormBase {

  /**
   * The Cts entity this selection condition belongs to.
   *
   * @var \Drupal\clever_theme_switcher\Entity\Cts
   */
  protected $entity;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cts_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the condition %name?', ['%name' => $this->condition->getPluginDefinition()['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->urlInfo('manage-conditions');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Cts $entity = NULL, $condition_id = NULL) {
    $this->entity = $entity;
    $this->condition = $entity->getCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->removeCondition($this->condition->getConfiguration()['uuid']);
    $this->entity->save();
    $this->messenger()->addMessage($this->t('The condition %name has been removed.', ['%name' => $this->condition->getPluginDefinition()['label']]));
    $form_state->setRedirect('entity.cts.manage_conditions', ['cts' => $this->entity->getId()]);
  }

}
