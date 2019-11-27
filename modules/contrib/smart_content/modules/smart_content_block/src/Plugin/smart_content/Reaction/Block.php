<?php

namespace Drupal\smart_content_block\Plugin\smart_content\Reaction;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\smart_content\Reaction\ReactionConfigurableBase;

/**
 * Provides smart_content reaction for display blocks.
 *
 * @SmartReaction(
 *   id = "block",
 *   label = @Translation("Block"),
 * )
 */
class Block extends ReactionConfigurableBase {

  /**
   * Block instance definition.
   *
   * @var mixed
   */
  protected $blockInstance;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Set unique id for element wrapper and submit name value.
    $settings_id = 'reaction-' . $this->id();
    $settings_id = Html::getUniqueId($settings_id);
    // If block defined, load block instance, otherwise select block.
    if ($block_instance = $this->getBlock()) {
      $label = 'Display Block: ' . $block_instance->getPluginDefinition()['admin_label'];
    }
    else {
      $label = 'Select Block to Display';
    }
    /* @var \Drupal\Core\Block\BlockManager $block_manager */
    $block_manager = \Drupal::service('plugin.manager.block');
    /* @var \Drupal\Core\Plugin\Context\LazyContextRepository $context_repository*/
    $context_repository = \Drupal::service('context.repository');
    // Only add blocks which work without any available context.
    $definitions = $block_manager->getDefinitionsForContexts($context_repository->getAvailableContexts());
    // Order by category, and then by admin label.
    $definitions = $block_manager->getSortedDefinitions($definitions);
    // Set options from available block definitions.
    $options = [];
    foreach ($definitions as $id => $definition) {
      $category = (string) $definition['category'];
      $options[$category][$id] = $definition['admin_label'];
    }
    $form['block_instance'] = [
      '#type' => 'fieldset',
      '#title' => $label,
      '#prefix' => '<div id="' . $settings_id . '">',
      '#suffix' => '</div>',
    ];

    // If block plugin exists get the block's configuration form.
    if ($block_instance = $this->getBlock()) {
      $form['block_instance'] += $block_instance->buildConfigurationForm([], $form_state);

      // Hide admin label (aka description).
      if (isset($form['block_instance']['admin_label'])) {
        unset($form['block_instance']['admin_label']);
      }

      $form['block_instance']['id'] = [
        '#type' => 'value',
        '#value' => $this->getConfiguration()['block_instance']['id'],
      ];
    }
    else {
      $form['block_instance']['id'] = [
        '#type' => 'select',
        // '#title' => $this->t('Block'),.
        '#title' => 'Block',
        '#title_display' => 'invisible',
        '#options' => $options,
        '#empty_option' => '- None -',
      ];
      $form['block_instance']['select_block'] = [
        '#type' => 'submit',
        '#value' => t('Select'),
        '#submit' => [[$this, 'addElementBlockConfiguration']],
        '#name' => 'select_block_' . $this->id(),
        '#ajax' => [
          'callback' => [$this, 'refreshElementBlock'],
          'wrapper' => $settings_id,
        ],
        '#limit_validation_errors' => [['block_instance', 'id']],
      ];
    }
    return $form;
  }

  /**
   * Load block instance form configuration.
   */
  public function getBlock() {
    $configuration = $this->getConfiguration();

    if (empty($configuration['block_instance']['id'])) {
      return NULL;
    }
    else {
      $block_plugin_id = $configuration['block_instance']['id'];
    }
    $block_configuration = isset($configuration['block_instance']) ? $configuration['block_instance'] : [];

    if (!isset($this->blockInstance)) {
      /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
      $block_manager = \Drupal::service('plugin.manager.block');

      /** @var \Drupal\Core\Block\BlockPluginInterface $block_instance */
      $block_instance = $block_manager->createInstance($block_plugin_id, $block_configuration);

      $plugin_definition = $block_instance->getPluginDefinition();

      // Don't return broken block plugin instances.
      if ($plugin_definition['id'] == 'broken') {
        return NULL;
      }

      // Don't return broken block content instances.
      if ($plugin_definition['id'] == 'block_content') {
        $uuid = $block_instance->getDerivativeId();
        if (!\Drupal::entityManager()
          ->loadEntityByUuid('block_content', $uuid)
        ) {
          return NULL;
        }
      }
      $this->blockInstance = $block_instance;
    }
    return $this->blockInstance;
  }

  /**
   * Ajax callback that return block configuration setting form.
   */
  public function addElementBlockConfiguration(array $form, FormStateInterface $form_state) {

    $button = $form_state->getTriggeringElement();
    $parents = array_slice($button['#parents'], 0, -2);

    $reaction_values = NestedArray::getValue($form_state->getUserInput(), $parents);
    $configuration = $this->getConfiguration();
    $configuration['block_instance']['id'] = $reaction_values['block_instance']['id'];
    $this->setConfiguration($configuration);
    $form_state->setRebuild();
  }

  /**
   * Ajax callback to reload block form element.
   */
  public function refreshElementBlock(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    return NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
  }

  /**
   * Returns smart_content block reaction content for winning variation.
   */
  public function getResponseContent() {

    $block_instance = $this->getBlock();
    // Make sure the block exists and is accessible.
    if (!$block_instance || !$block_instance->access(\Drupal::currentUser())) {
      return [];
    }

    // @see \Drupal\block\BlockViewBuilder::buildPreRenderableBlock
    // @see template_preprocess_block()
    return [
      '#theme' => 'block',
      '#attributes' => [],
      '#configuration' => $block_instance->getConfiguration(),
      '#plugin_id' => $block_instance->getPluginId(),
      '#base_plugin_id' => $block_instance->getBaseId(),
      '#derivative_plugin_id' => $block_instance->getDerivativeId(),
      '#id' => $this->getPluginId(),
      'content' => $block_instance->build(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->getBlock()) {
      $subform_state = SubformState::createForSubform($form['block_instance'], $form, $form_state);
      $this->getBlock()->submitConfigurationForm($form['block_instance'], $subform_state);
      $configuration = [
        'block_instance' => $this->getBlock()->getConfiguration(),
      ];
      $this->setConfiguration($configuration);
    }
  }

}
