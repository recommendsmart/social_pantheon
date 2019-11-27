<?php

namespace Drupal\smart_content_block\Plugin\smart_content\VariationSetType;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_content\Form\SmartVariationSetForm;
use Drupal\smart_content\VariationSetType\VariationSetTypeBase;

/**
 * Defines a 'SmartVariationSetType' plugin for Blocks.
 *
 * Provides a 'SmartVariationSetType' for blocks.  Includes a plugin
 * configuration form for configuring variations.
 *
 * @SmartVariationSetType(
 *   id = "block",
 *   label = @Translation("Block"),
 * )
 */
class Block extends VariationSetTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $wrapper_id = Html::getUniqueId('variation-set-wrapper');
    $wrapper_items_id = Html::getUniqueId('variation-set-items-wrapper');

    $form['variations_config'] = [
      '#type' => 'container',
      '#title' => 'Variations',
      '#tree' => TRUE,
      '#prefix' => '<div id="' . $wrapper_id . '" class="variations-container">',
      '#suffix' => '</div>',
    ];
    $form['variations_config']['variation_items'] = [
      '#type' => 'table',
      '#header' => [t('Variations'), t('Weight'), ''],
      '#prefix' => '<div id="' . $wrapper_items_id . '" class="variations-container-items">',
      '#suffix' => '</div>',
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $wrapper_items_id . '-order-weight',
        ],
      ],
    ];

    $i = 0;
    foreach ($this->entity->getVariations() as $variation_id => $variation) {
      $i++;

      SmartVariationSetForm::pluginForm($variation, $form, $form_state, [
        'variations_config',
        'variation_items',
        $variation_id,
        'plugin_form',
      ]);

      $form['variations_config']['variation_items'][$variation_id]['plugin_form']['#type'] = 'fieldset';
      $form['variations_config']['variation_items'][$variation_id]['plugin_form']['#title'] = 'Variation ' . $i;
      $form['variations_config']['variation_items'][$variation_id]['plugin_form']['#attributes']['class'][] = 'variation-container';
      $form['variations_config']['variation_items'][$variation_id]['#attributes']['class'][] = 'draggable';
      $form['variations_config']['variation_items'][$variation_id]['#weight'] = $variation->getWeight();

      $form['variations_config']['variation_items'][$variation_id]['weight'] = [
        '#type' => 'weight',
        '#title' => 'Weight',
        '#title_display' => 'invisible',
        '#attributes' => ['class' => [$wrapper_items_id . '-order-weight']],
      ];

      $form['variations_config']['variation_items'][$variation_id]['remove_variation'] = [
        '#type' => 'submit',
        '#value' => t('Remove Variation'),
        '#name' => 'remove_variation__' . $variation_id,
        '#submit' => [[$this, 'removeElementVariation']],
        '#attributes' => [
          'class' => [
            'align-right',
            'remove-variation',
            'remove-button',
          ],
        ],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => [$this, 'removeElementVariationAjax'],
          'wrapper' => $wrapper_id,
        ],
      ];
      $disabled = ($this->entity->getDefaultVariation() && $this->entity->getDefaultVariation() != $variation_id) ? 'disabled' : '';
      $form['variations_config']['variation_items'][$variation_id]['plugin_form']['additional_settings'] = [
        '#type' => 'container',
        '#weight' => 10,
        '#attributes' => [
          'class' => ['variation-additional-settings-container'],
          'disabled' => [$disabled],
        ],
      ];
      $form['variations_config']['variation_items'][$variation_id]['plugin_form']['additional_settings']['default_variation'] = [
        '#type' => 'checkbox',
        '#attributes' => [
          'class' => ['smart-variations-default-' . $variation_id],
          'disabled' => [$disabled],
        ],
        '#title' => 'Set as default variation',
        '#default_value' => $this->entity->getDefaultVariation() == $variation_id,
      ];
    }

    $form['variations_config']['add_variation'] = [
      '#type' => 'submit',
      '#value' => t('Add Variation'),
      '#submit' => [[$this, 'addElementVariation']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'addElementVariationAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->entity->getVariations() as $variation_id => $variation) {
      SmartVariationSetForm::pluginFormValidate($variation, $form, $form_state, [
        'variations_config',
        'variation_items',
        $variation_id,
        'plugin_form',
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->attachTableVariationWeight($form_state->getValues()['variations_config']['variation_items']);
    foreach ($this->entity->getVariations() as $variation_id => $variation) {
      SmartVariationSetForm::pluginFormSubmit($variation, $form, $form_state, [
        'variations_config',
        'variation_items',
        $variation_id,
        'plugin_form',
      ]);
    }
    $this->attachFormValues($form_state->getValues()['variations_config']['variation_items']);
  }

  /**
   * Attaches values from $form_state to entity.
   *
   * Updates the 'default_variation' on the entity based on the values from
   * $form_state.  Will  remove the existing 'default_variation' if a new
   * one has been defined.
   */
  public function attachFormValues($values) {
    foreach ($this->entity->getVariations() as $variation) {
      // Attaching default_variation value to variation config.
      if (isset($values[$variation->id()]['plugin_form']['additional_settings']['default_variation'])) {
        if ($values[$variation->id()]['plugin_form']['additional_settings']['default_variation']) {
          $this->entity->setDefaultVariation($variation->id());
        }
        else {
          if ($variation->id() == $this->entity->getDefaultVariation()) {
            $this->entity->setDefaultVariation('');
          }
        }
      }
    }
  }

  /**
   * Provides a '#submit' callback for adding a Variation.
   */
  public function addElementVariation(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $items = array_slice($button['#parents'], 0, -1);
    $items[] = 'variation_items';
    $values = NestedArray::getValue($form_state->getUserInput(), $items);
    $this->entity->addVariation(\Drupal::service('plugin.manager.smart_content.variation')
      ->createInstance('variation_block', [], $this->entity));
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for adding a Variation.
   */
  public function addElementVariationAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
  }

  /**
   * Provides a '#submit' callback for removing a Variation.
   */
  public function removeElementVariation(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $items = array_slice($button['#parents'], 0, -3);
    $items[] = 'variation_items';
    $values = NestedArray::getValue($form_state->getUserInput(), $items);
    $this->attachTableVariationWeight($values);
    list($action, $name) = explode('__', $form_state->getTriggeringElement()['#name']);
    $this->entity->removeVariation($name);
    $form_state->setRebuild();
  }

  /**
   * Provides an '#ajax' callback for removing a Variation.
   */
  public function removeElementVariationAjax(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));
  }

  /**
   * Gets the dependencies associated with the given smart variation set.
   *
   * @param string $id
   *   The smart variation set ID.
   *
   * @return array|bool
   *   An array of blocks that contains the provided variation set, FALSE if no
   *   blocks contain the given variation set.
   */
  public function getDependencies($id) {
    try {
      $block_storage = \Drupal::entityTypeManager()->getStorage('block');
    }
    catch (PluginNotFoundException | InvalidPluginDefinitionException $e) {
      return FALSE;
    }
    // Load all smart block config entities and find the one that encloses the
    // provided smart variation set.
    $block_ids = $block_storage->getQuery()
      ->condition('plugin', 'smart_block')
      ->execute();
    if (!empty($block_ids)) {
      foreach ($block_ids as $block_id) {
        $block = $block_storage->load($block_id);
        $settings = $block->get('settings');
        if ($settings['variation_set'] === $id) {
          return ['block.block.' . $block_id];
        }
      }
    }
    return FALSE;
  }

}
