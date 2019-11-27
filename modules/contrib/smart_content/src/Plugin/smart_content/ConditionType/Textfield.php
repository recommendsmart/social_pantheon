<?php

namespace Drupal\smart_content\Plugin\smart_content\ConditionType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Condition\ConditionBase;
use Drupal\smart_content\ConditionType\ConditionTypeBase;

/**
 * Provides a 'textfield' ConditionType.
 *
 * @SmartConditionType(
 *  id = "textfield",
 *  label = @Translation("Textfield"),
 * )
 */
class Textfield extends ConditionTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $condition_definition = $this->conditionInstance->getPluginDefinition();

    $form = ConditionBase::attachNegateElement($form, $this->configuration);

    if (!isset($form['#attributes']['class'])) {
      $form['#attributes']['class'] = [];
    }
    $form['#attributes']['class'][] = 'condition-textfield';

    $form['label'] = [
      '#type' => 'container',
      // @todo: get condition group name from group
      '#markup' => $condition_definition['label'] . '(' . $condition_definition['group'] . ')',
      '#attributes' => ['class' => ['condition-label']],
    ];
    $form['op'] = [
      '#type' => 'select',
      '#options' => $this->getOperators(),
      '#default_value' => isset($this->configuration['op']) ? $this->configuration['op'] : $this->defaultFieldConfiguration()['op'],
      '#attributes' => ['class' => ['condition-op']],
    ];
    $form['value'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => isset($this->configuration['value']) ? $this->configuration['value'] : $this->defaultFieldConfiguration()['value'],
      '#attributes' => ['class' => ['condition-value']],
      // @todo: make configurable
      '#size' => 20,
    ];

    $form['#process'][] = [$this, 'buildWidget'];
    return $form;
  }

  /**
   * Process callback for accessing parents.
   */
  public function buildWidget(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($element['#parents'])) {
      $parents = $element['#parents'];
      $first_item = array_shift($parents);

      array_walk($parents, function (&$value, $i) {
        $value = '[' . $value . ']';
      });

      $parent_string = $first_item . implode('', $parents) . '[op]';

      $element['value']['#states'] = [
        'invisible' => [
          'select[name="' . $parent_string . '"]' => ['value' => 'empty'],
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFieldConfiguration() {
    return [
      'op' => 'equals',
      'value' => '',
    ];
  }

  /**
   * Returns a list of operators.
   */
  public function getOperators() {
    return [
      'equals' => 'Equals',
      'starts_with' => 'Starts with',
      'empty' => 'Is empty',
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
