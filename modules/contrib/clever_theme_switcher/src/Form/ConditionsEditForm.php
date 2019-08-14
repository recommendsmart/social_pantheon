<?php

namespace Drupal\clever_theme_switcher\Form;

/**
 * Provides a form for editing an condition.
 */
class ConditionsEditForm extends ConditionFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cts_condition_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    return $this->entity->getCondition($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label condition has been updated.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
