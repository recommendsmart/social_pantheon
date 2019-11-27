<?php

namespace Drupal\smart_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\smart_content\Variation\VariationInterface;
use Drupal\smart_content\VariationSetType\VariationSetTypeInterface;

/**
 * Defines the Smart variation set entity.
 *
 * @ConfigEntityType(
 *   id = "smart_variation_set",
 *   label = @Translation("Smart variation set"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\smart_content\Form\SmartVariationSetForm",
 *       "edit" = "Drupal\smart_content\Form\SmartVariationSetForm",
 *     },
 *   },
 *   config_prefix = "smart_variation_set",
 *   admin_permission = "administer smart content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "variations_settings",
 *     "variation_set_type_settings",
 *     "default_variation",
 *   }
 * )
 */
class SmartVariationSet extends ConfigEntityBase implements SmartVariationSetInterface {

  /**
   * The Smart variation set ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Smart variation set label.
   *
   * @var string
   */
  protected $label;

  /**
   * The variations associated with this variation set.
   *
   * @var \Drupal\smart_content\Variation\VariationInterface[]
   */
  protected $variations;

  /**
   * Settings for the wrapper class for this entity.
   *
   * @var array
   */
  protected $variation_set_type_settings;

  /**
   * The wrapper object for this entity.
   *
   * @var \Drupal\smart_content\VariationSetType\VariationSetTypeInterface
   */
  protected $variationSetTypeInstance;

  /**
   * The decision agent for this entity. Defaults to 'client'.
   *
   * @var \Drupal\smart_content\DecisionAgent\DecisionAgentInterface
   */
  protected $decisionAgentInstance;

  /**
   * {@inheritdoc}
   */
  public function getVariationSetType() {
    if (!isset($this->variationSetTypeInstance)) {
      $this->variationSetTypeInstance = NULL;
      $instance = $this->getVariationSetTypeFromSettings();
      if ($instance instanceof VariationSetTypeInterface) {
        $this->setVariationSetType($instance);
      }
    }
    return $this->variationSetTypeInstance;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariationSetType(VariationSetTypeInterface $variation_set_type) {
    $this->variationSetTypeInstance = $variation_set_type;
  }

  /**
   * {@inheritdoc}
   */
  public function addVariation(VariationInterface $variation) {
    if ($variation->id() === NULL) {
      $variation->setId($this->generateUniquePluginId($variation, array_keys($this->getVariations())));
    }
    // @todo: find better way to do this.
    $this->variations[$variation->id()] = $variation;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariations() {
    if (!isset($this->variations)) {
      $this->variations = [];
      foreach ($this->getVariationsFromSettings() as $plugin) {
        $this->addVariation($plugin);
      }
    }
    return $this->variations;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariation($id) {
    foreach ($this->getVariations() as $variation) {
      if ($variation->id() == $id) {
        return $variation;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariation($id) {
    unset($this->variations[$id]);
    // Unset default_variation if variation removed.
    $this->default_variation = $this->getDefaultVariation();
  }

  /**
   * Sorts variations based on weight.
   *
   * Temporary solution for handling variation order until core bug is fixed
   * for tables and weight.
   */
  public function sortVariations() {
    if ($this->getVariations()) {
      uasort($this->variations, function ($first, $second) {
        return $first->getWeight() > $second->getWeight();
      });
    }
  }

  /**
   * Adds variation settings and wrapper object settings.
   */
  protected function writeChangesToSettings() {
    if ($variation_set_type = $this->getVariationSetType()) {
      $variation_set_type->writeChangesToConfiguration();
      $this->variation_set_type_settings = $variation_set_type->getConfiguration();
    }
    $variations_settings = [];
    foreach ($this->getVariations() as $variation) {
      $variation->writeChangesToConfiguration();
      $variations_settings[] = $variation->getConfiguration();
    }
    $this->variations_settings = $variations_settings;
    $this->default_variation = $this->getDefaultVariation();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $this->writeChangesToSettings();

    if (empty($this->id)) {
      $this->id = \Drupal::service('uuid')->generate();
    }
    parent::save();
  }

  /**
   * Create a new wrapper object using the settings stored on this entity.
   *
   * @return \Drupal\smart_content\VariationSetType\VariationSetTypeInterface|null
   *   A VariationSetType instance if set.
   */
  protected function getVariationSetTypeFromSettings() {
    if (!empty($this->variation_set_type_settings)) {
      return \Drupal::service('plugin.manager.smart_content.variation_set_type')
        ->createInstance($this->variation_set_type_settings['plugin_id'], $this->variation_set_type_settings, $this);
    }
    return NULL;
  }

  /**
   * Gets an array of plugins instantiated from the settings.
   *
   * @return \Drupal\smart_content\Variation\VariationInterface[]
   *   An array of Variation instances from configuration.
   */
  protected function getVariationsFromSettings() {
    $plugins = [];
    if (!empty($this->variations_settings)) {
      foreach ($this->variations_settings as $id => $value) {
        $plugins[] = \Drupal::getContainer()
          ->get('plugin.manager.smart_content.variation')
          ->createInstance($value['plugin_id'], $value, $this);
      }
    }
    return $plugins;
  }

  /**
   * Generates a unique ID for variation plugins.
   *
   * @param mixed $plugin
   *   A plugin.
   * @param array $existing_ids
   *   An array of existing plugin ids.
   *
   * @return string
   *   An id unique from the existing passed ones.
   */
  public static function generateUniquePluginId($plugin, array $existing_ids) {
    $count = 1;
    $machine_default = $plugin->getPluginId();
    while (in_array($machine_default, $existing_ids)) {
      $machine_default = $plugin->getPluginId() . '_' . ++$count;
    }
    return $machine_default;
  }

  /**
   * Get decision agent.
   *
   * @return \Drupal\smart_content\DecisionAgent\DecisionAgentInterface
   *   A DecisionAgent instance.
   */
  public function getDecisionAgent() {
    if (!isset($this->decisionAgentInstance)) {
      $this->decisionAgentInstance = \Drupal::service('plugin.manager.smart_content.decision_agent')
        ->createInstance('client', [], $this);
    }
    return $this->decisionAgentInstance;
  }

  /**
   * Render placeholder and attach libraries and settings.
   *
   * @return mixed
   *   Render array.
   */
  public function renderPlaceholder($context = []) {
    $content = $this->getDecisionAgent()->renderPlaceholder($context);
    $content['#attached'] = [
      'library' => $this->getLibraries(),
      'drupalSettings' => [
        'smartContentDecisions' => $this->getAttachedSettings(),
      ],
    ];
    return $content;
  }

  /**
   * Get JS libraries for all child instances.
   *
   * @return array
   *   Array of Drupal libraries.
   */
  public function getLibraries() {
    $libraries = [];
    foreach ($this->getVariations() as $variation) {
      $libraries = array_unique(array_merge($libraries, $variation->getLibraries()));
    }
    $libraries = array_unique(array_merge($libraries, $this->getDecisionAgent()
      ->getLibraries()));
    return $libraries;
  }

  /**
   * Get JS drupalSettings for all child instances.
   *
   * @return array
   *   Array of JS settings from self and variations.
   */
  public function getAttachedSettings() {
    $settings = [];
    foreach ($this->getVariations() as $variation) {
      $settings[] = $variation->getAttachedSettings();
    }
    return [
      $this->getPlaceholderDecisionId() => [
        'name' => $this->getPlaceholderDecisionId(),
        'agent' => $this->getDecisionAgent()->getPluginId(),
        'variations' => $settings,
      ],
    ];
  }

  /**
   * Get placeholder for attribute.
   *
   * @return string
   *   A placeholder decision id.
   */
  public function getPlaceholderDecisionId() {
    return $this->getEntityTypeId() . '.' . $this->id();
  }

  /**
   * Get the response for the specified variation.
   *
   * @param string $id
   *   Variation id.
   * @param array $context
   *   Context from request for variation processing.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|null
   *   AJAX response for ajax request.
   */
  public function getVariationResponse($id, array $context = []) {
    if ($variation = $this->getVariation($id)) {
      return $variation->getResponse($context);
    }
    return NULL;
  }

  /**
   * Validate requested Reaction.
   *
   * Checks if the reaction is accessible and valid, eg. checking if the user
   * has access to view the reaction entity.
   *
   * @param string $variation_id
   *   Variation id.
   * @param array $context
   *   Context from request for variation processing.
   *
   * @return bool
   *   Validation result.
   */
  public function validateReactionRequest($variation_id, array $context = []) {
    if ($this->getVariation($variation_id)) {
      return $this->getVariationSetType()->validateReactionRequest($variation_id, $context = []);
    }
    return FALSE;
  }

  /**
   * Get the default winner for this variation set.
   *
   * @return string|null
   *   Returns default variation if defined.
   */
  public function getDefaultVariation() {
    if (!empty($this->default_variation)) {
      // Confirm variation exists.
      if ($this->getVariation($this->default_variation)) {
        return $this->default_variation;
      }
    }
  }

  /**
   * Set a default variation to be selected as the winner.
   *
   * @param string $variation_id
   *   Variation ID.
   */
  public function setDefaultVariation($variation_id) {
    $this->default_variation = $variation_id;
  }

  /**
   * {@inheritDoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $variationSetType = $this->getVariationSetType();
    if (!is_null($variationSetType)) {
      $dependencies = $variationSetType->getDependencies($this->id);
      if ($dependencies) {
        foreach ($dependencies as $dependency) {
          $this->addDependency('config', $dependency);
        }
      }
    }
    return $this;
  }

}
