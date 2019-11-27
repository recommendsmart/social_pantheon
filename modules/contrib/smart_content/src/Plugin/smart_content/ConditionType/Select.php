<?php

namespace Drupal\smart_content\Plugin\smart_content\ConditionType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Condition\ConditionBase;
use Drupal\smart_content\ConditionType\ConditionTypeBase;

/**
 * Provides a 'number' ConditionType.
 *
 * @SmartConditionType(
 *  id = "select",
 *  label = @Translation("Select"),
 * )
 */
class Select extends ConditionTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $condition_definition = $this->conditionInstance->getPluginDefinition();
    $form = ConditionBase::attachNegateElement($form, $this->configuration);

    $options = [];
    // If 'options' are defined in definition, populate options.
    if (isset($condition_definition['options'])) {
      $options = $condition_definition['options'];
    }
    // If 'options_callback' is defined in definition, validate and populate
    // options.
    elseif (isset($condition_definition['options_callback'])) {
      // Confirm 'options_callback' is callable function/method.
      if (is_callable($condition_definition['options_callback'], FALSE, $callable_name)) {
        $options = call_user_func($condition_definition['options_callback']);
      }
    }
    $form['label'] = [
      '#type' => 'container',
      // @todo: get condition group name from group
      '#markup' => $condition_definition['label'] . '(' . $condition_definition['group'] . ')',
      '#attributes' => ['class' => ['condition-label']],
    ];
    $form['value'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => isset($this->configuration['value']) ? $this->configuration['value'] : $this->defaultFieldConfiguration()['value'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFieldConfiguration() {
    return [
      'value' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return ['smart_content/condition_type.standard'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    return $this->getConfiguration() + $this->defaultFieldConfiguration();
  }

}
