<?php

namespace Drupal\clever_theme_switcher\Helper;

use Drupal\clever_theme_switcher\Entity\Cts;
use Drupal\Core\Url;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Serialization\Json;

/**
 * Condition form helper.
 */
trait ConditionsFormHelper {

  /**
   * Get default attributes.
   */
  protected function getAttributes() {
    return [
      'class' => [
        'button',
        'button--small',
        'form-item',
      ],
    ];
  }

  /**
   * Conditions helper function.
   */
  protected function helper(array $form, Cts $entity) {
    $form['condition_collection'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#attributes' => ['id' => 'edit-condition-collection'],
      '#open' => TRUE,
      '#weight' => 10,
    ];
    $form['condition_collection']['conditions'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Plugin name'),
        $this->t('Description'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('There are no conditions.'),
    ];

    if ($conditions = $entity->getConditions()) {
      foreach ($conditions as $condition_id => $condition) {
        $row = [];
        $row['label']['#markup'] = $condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('conditions.edit', [
            'entity' => $entity->getId(),
            'plugin_id' => $condition_id,
          ]),
          'attributes' => NestedArray::mergeDeep($this->getAttributes(), [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 'auto',
            ]),
          ]),
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('conditions.delete', [
            'entity' => $entity->getId(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $this->getAttributes(),
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['condition_collection']['conditions'][$condition_id] = $row;
      }
    }

    return $form;
  }

}
