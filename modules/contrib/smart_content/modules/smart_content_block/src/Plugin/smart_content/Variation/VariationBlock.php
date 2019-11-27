<?php

namespace Drupal\smart_content_block\Plugin\smart_content\Variation;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\smart_content\Form\SmartVariationSetForm;
use Drupal\smart_content\Variation\VariationBase;

/**
 * Defines a 'SmartVariation' plugin.
 *
 * Provides a 'SmartVariation' plugin for configuring Variations conditions
 * and Block reactions.
 *
 * @SmartVariation(
 *   id = "variation_block",
 *   label = @Translation("View Mode Variation"),
 * )
 */
class VariationBlock extends VariationBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $wrapper_id = Html::getUniqueId('variation-wrapper');
    $wrapper_items_id = Html::getUniqueId('variation-items-wrapper');

    $form['conditions_config'] = [
      '#type' => 'container',
      '#title' => t('Condition(s)'),
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $wrapper_id . '-conditions' . '" class="conditions-container variation-conditions-container">',
      '#suffix' => '</div>',
    ];
    $form['conditions_config']['condition_items'] = [
      '#type' => 'table',
      '#header' => [t('Condition(s)'), t('Weight'), ''],
      '#prefix' => '<div id="' . $wrapper_items_id . '-conditions' . '" class="conditions-container-items variation-conditions-containers-items">',
      '#suffix' => '</div>',
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $wrapper_items_id . '-order-condition-weight',
        ],
      ],
    ];
    foreach ($this->getConditions() as $condition_id => $condition) {
      if ($condition instanceof PluginFormInterface) {
        SmartVariationSetForm::pluginForm($condition, $form, $form_state, [
          'conditions_config',
          'condition_items',
          $condition_id,
          'plugin_form',
        ]);

        $form['conditions_config']['condition_items'][$condition_id]['plugin_form']['#type'] = 'container';
        $form['conditions_config']['condition_items'][$condition_id]['plugin_form']['#title'] = $condition->getPluginId();
        $form['conditions_config']['condition_items'][$condition_id]['plugin_form']['#attributes']['class'][] = 'condition';
        $form['conditions_config']['condition_items'][$condition_id]['#weight'] = $condition->getWeight();

        $form['conditions_config']['condition_items'][$condition_id]['#attributes']['class'][] = 'draggable';

        $form['conditions_config']['condition_items'][$condition_id]['weight'] = [
          '#type' => 'weight',
          '#title' => 'Weight',
          '#title_display' => 'invisible',
          '#default_value' => $condition->getWeight(),
          '#attributes' => ['class' => [$wrapper_items_id . '-order-condition-weight']],
        ];

        $form['conditions_config']['condition_items'][$condition_id]['remove_condition'] = [
          '#type' => 'submit',
          '#value' => t('Remove Condition'),
          '#name' => 'remove_condition_' . $this->id() . '__' . $condition_id,
          '#submit' => [[$this, 'removeElementCondition']],
          '#attributes' => [
            'class' => [
              'align-right',
              'remove-condition',
              'remove-button',
            ],
          ],
          '#limit_validation_errors' => [],
        ];

        $form['conditions_config']['condition_items'][$condition_id]['remove_condition']['#ajax'] = [
          'callback' => [$this, 'removeElementConditionAjax'],
          'wrapper' => $wrapper_id . '-conditions',
        ];
      }
    }

    $form['conditions_config']['add_condition'] = [
      '#type' => 'container',
      '#title' => 'Add Condition',
      '#attributes' => ['class' => ['condition-add-container']],
      '#process' => [[$this, 'processConditionLimitValidation']],
    ];
    $form['conditions_config']['add_condition']['condition_type'] = [
      '#title' => 'Condition Type',
      '#title_display' => 'invisible',
      '#type' => 'select',
      '#options' => $this->conditionManager->getFormOptions(),
      '#empty_value' => '',
    ];
    $form['conditions_config']['add_condition']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add Condition'),
      '#validate' => [[$this, 'addElementConditionValidate']],
      '#submit' => [[$this, 'addElementCondition']],
      '#name' => 'add_condition_' . $this->id(),
      '#ajax' => [
        'callback' => [$this, 'addElementConditionAjax'],
        'wrapper' => $wrapper_id . '-conditions',
      ],
    ];

    $form['reactions_config'] = [
      '#type' => 'container',
      '#title' => 'Reactions',
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $wrapper_id . '-reactions">',
      '#suffix' => '</div>',
    ];

    $form['reactions_config']['reaction_items'] = [
      '#type' => 'table',
      '#header' => [t('Reaction(s)'), t('Weight'), ''],
      '#prefix' => '<div id="' . $wrapper_items_id . '-reactions">',
      '#suffix' => '</div>',
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $wrapper_items_id . '-order-reaction-weight',
        ],
      ],
    ];

    foreach ($this->getReactions() as $reaction_id => $reaction) {
      if ($reaction instanceof PluginFormInterface) {
        SmartVariationSetForm::pluginForm($reaction, $form, $form_state, [
          'reactions_config',
          'reaction_items',
          $reaction_id,
          'plugin_form',
        ]);

        $form['reactions_config']['reaction_items'][$reaction_id]['plugin_form']['#type'] = 'container';
        $form['reactions_config']['reaction_items'][$reaction_id]['plugin_form']['#title'] = $reaction->getPluginId();
        $form['reactions_config']['reaction_items'][$reaction_id]['plugin_form']['#attributes']['class'][] = 'reaction';
        $form['reactions_config']['reaction_items'][$reaction_id]['#weight'] = $reaction->getWeight();

        $form['reactions_config']['reaction_items'][$reaction_id]['#attributes']['class'][] = 'draggable';
        $form['reactions_config']['reaction_items'][$reaction_id]['#attributes']['class'][] = 'row-reaction';

        $form['reactions_config']['reaction_items'][$reaction_id]['weight'] = [
          '#type' => 'weight',
          '#title' => 'Weight',
          '#title_display' => 'invisible',
          '#default_value' => $reaction->getWeight(),
          '#attributes' => ['class' => [$wrapper_items_id . '-order-reaction-weight']],
        ];

        $form['reactions_config']['reaction_items'][$reaction_id]['remove_reaction'] = [
          '#type' => 'submit',
          '#value' => t('Remove Reaction'),
          '#name' => 'remove_reaction_' . $this->id() . '__' . $reaction_id,
          '#submit' => [[$this, 'removeElementReaction']],
          '#attributes' => [
            'class' => [
              'align-right',
              'remove-reaction',
              'remove-button',
            ],
          ],
          '#limit_validation_errors' => [],
        ];
        $form['reactions_config']['reaction_items'][$reaction_id]['remove_reaction']['#ajax'] = [
          'callback' => [$this, 'removeElementReactionAjax'],
          'wrapper' => $wrapper_id . '-reactions',
        ];
      }
    }
    $form['reactions_config']['add_reaction'] = [
      '#type' => 'container',
    ];

    $form['reactions_config']['add_reaction'] = [
      '#type' => 'container',
      '#title' => 'Add Reaction',
    ];

    $form['reactions_config']['add_reaction']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add Reaction'),
      '#submit' => [[$this, 'addElementReaction']],
      '#name' => 'add_reaction_' . $this->id(),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'addElementReactionAjax'],
        'wrapper' => $wrapper_id . '-reactions',
      ],
    ];
    return $form;
  }

  /**
   * Render API callback: builds the formatter settings elements.
   */
  public function processConditionLimitValidation(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['submit']['#limit_validation_errors'] = [array_merge($element['#array_parents'], ['condition_type'])];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $conditions = $this->getConditions();
    if (empty($conditions)) {
      $form_state->setErrorByName('conditions_config', 'You must enter a condition.');
    }
    foreach ($conditions as $condition_id => $condition) {
      SmartVariationSetForm::pluginFormValidate($condition, $form, $form_state, [
        'conditions_config',
        'condition_items',
        $condition_id,
        'plugin_form',
      ]);
    }
    $reactions = $this->getReactions();
    if (empty($reactions)) {
      $form_state->setErrorByName('conditions_config', 'You must enter a reaction.');
    }
    foreach ($reactions as $reaction_id => $reaction) {
      if ($reaction instanceof PluginFormInterface) {
        SmartVariationSetForm::pluginFormValidate($reaction, $form, $form_state, [
          'reactions_config',
          'reaction_items',
          $reaction_id,
          'plugin_form',
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    self::attachTableConditionWeight($form_state->getValues()['conditions_config']['condition_items']);
    self::attachTableReactionWeight($form_state->getValues()['reactions_config']['reaction_items']);
    foreach ($this->getConditions() as $condition_id => $condition) {
      SmartVariationSetForm::pluginFormSubmit($condition, $form, $form_state, [
        'conditions_config',
        'condition_items',
        $condition_id,
        'plugin_form',
      ]);
    }
    foreach ($this->getReactions() as $reaction_id => $reaction) {
      if ($reaction instanceof PluginFormInterface) {
        SmartVariationSetForm::pluginFormSubmit($reaction, $form, $form_state, [
          'reactions_config',
          'reaction_items',
          $reaction_id,
          'plugin_form',
        ]);
      }
    }
  }

  /**
   * Provides a '#validate' callback for adding a Condition.
   *
   * Validates that a valid condition type is selected.
   */
  public function addElementConditionValidate(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $parents = array_slice($button['#parents'], 0, -1);
    $parents[] = 'condition_type';
    if (!$value = NestedArray::getValue($form_state->getUserInput(), $parents)) {
      $form_state->setError(NestedArray::getValue($form, $parents), 'Condition type required.');
    }
  }

  /**
   * Provides a '#submit' callback for adding a Condition.
   */
  public function addElementCondition(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Save condition weight.
    $condition_values = NestedArray::getValue($form_state->getUserInput(), array_slice($button['#parents'], 0, -2));
    if (isset($condition_values['condition_items'])) {
      $this->attachTableConditionWeight($condition_values['condition_items']);
    }

    $type = NestedArray::getValue($form_state->getUserInput(), array_slice($button['#parents'], 0, -1))['condition_type'];
    $this->addCondition($this->conditionManager->createInstance($type));
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for adding a Condition.
   */
  public function addElementConditionAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
  }

  /**
   * Provides a '#submit' callback for removing a Condition.
   */
  public function removeElementCondition(array &$form, FormStateInterface $form_state) {

    $button = $form_state->getTriggeringElement();

    list($action, $name) = explode('__', $button['#name']);

    // Save condition weight.
    $condition_values = NestedArray::getValue($form_state->getUserInput(), array_slice($button['#parents'], 0, -3));
    if (isset($condition_values['condition_items'])) {
      $this->attachTableConditionWeight($condition_values['condition_items']);
    }

    $variation = $this->entity->getVariation($this->id());
    $variation->removeCondition($name);
    $form_state->setRebuild();

  }

  /**
   * Provides an '#ajax' callback for removing a Condition.
   */
  public function removeElementConditionAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));
  }

  /**
   * Provides a '#submit' callback for adding a Reaction.
   */
  public function addElementReaction(array &$form, FormStateInterface $form_state) {
    // @todo: reorder reactions to account for drupal core issue.
    $button = $form_state->getTriggeringElement();

    // Save reaction weight.
    $reaction_values = NestedArray::getValue($form_state->getUserInput(), array_slice($button['#parents'], 0, -2));
    if (isset($reaction_values['reaction_items'])) {
      $this->attachTableReactionWeight($reaction_values['reaction_items']);
    }
    $this->addReaction($this->reactionManager->createInstance('block', [], $this->entity));
    $this->entity->addVariation($this);
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for adding a Reaction.
   */
  public function addElementReactionAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
  }

  /**
   * Provides a '#submit' callback for removing a Reaction.
   */
  public function removeElementReaction(array &$form, FormStateInterface $form_state) {

    $button = $form_state->getTriggeringElement();

    list($action, $name) = explode('__', $button['#name']);

    // Save reaction weight.
    $reaction_values = NestedArray::getValue($form_state->getUserInput(), array_slice($button['#parents'], 0, -3));
    if (isset($reaction_values['reaction_items'])) {
      $this->attachTableReactionWeight($reaction_values['reaction_items']);
    }

    $variation = $this->entity->getVariation($this->id());
    $variation->removeReaction($name);
    $form_state->setRebuild();

  }

  /**
   * Provides a '#submit' callback for removing a Reaction.
   */
  public function removeElementReactionAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));
  }

}
