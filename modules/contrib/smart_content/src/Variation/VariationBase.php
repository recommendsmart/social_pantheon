<?php

namespace Drupal\smart_content\Variation;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\smart_content\Condition\ConditionInterface;
use Drupal\smart_content\Condition\ConditionManager;
use Drupal\smart_content\Entity\SmartVariationSet;
use Drupal\smart_content\Reaction\ReactionInterface;
use Drupal\smart_content\Reaction\ReactionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Base class for Smart variation plugins.
 */
abstract class VariationBase extends PluginBase implements VariationInterface, ConfigurablePluginInterface, PluginFormInterface, ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * The Variation weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * ConditionManager.
   *
   * @var \Drupal\smart_content\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * ReactionManager.
   *
   * @var \Drupal\smart_content\Reaction\ReactionManager
   */
  protected $reactionManager;

  /**
   * The Condition instances.
   *
   * @var \Drupal\smart_content\Condition\ConditionInterface[]
   */
  protected $conditions;

  /**
   * The Reaction instances.
   *
   * @var \Drupal\smart_content\Reaction\ReactionInterface[]
   */
  protected $reactions;

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.smart_content.condition'),
      $container->get('plugin.manager.smart_content.reaction')
    );
  }

  /**
   * Constructor with condition and reaction managers.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\smart_content\Condition\ConditionManager $condition_manager
   *   The condition manager.
   * @param \Drupal\smart_content\Reaction\ReactionManager $reaction_manager
   *   The reaction manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConditionManager $condition_manager, ReactionManager $reaction_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->conditionManager = $condition_manager;
    $this->reactionManager = $reaction_manager;
  }

  /**
   * Getter for id.
   */
  public function id() {
    return isset($this->configuration['id']) ? $this->configuration['id'] : NULL;
  }

  /**
   * Setter for id.
   *
   * @param string $id
   *   The Variations ID.
   */
  public function setId($id) {
    $configuration = $this->getConfiguration();
    $configuration['id'] = $id;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'id' => $this->id(),
      'plugin_id' => $this->getPluginId(),
      'weight' => $this->getWeight(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // TODO: Implement calculateDependencies() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * Setter for Variation weight.
   */
  public function setWeight($weight) {
    $configuration = $this->getConfiguration();
    $configuration['weight'] = $weight;
    $this->setConfiguration($configuration);
  }

  /**
   * Getter for Variation weight.
   */
  public function getWeight() {
    return isset($this->configuration['weight']) ? $this->configuration['weight'] : 0;
  }

  /**
   * Add Condition to Variation.
   *
   * @param \Drupal\smart_content\Condition\ConditionInterface $condition
   *   A condition to be added to the Variation.
   */
  public function addCondition(ConditionInterface $condition) {
    if ($condition->id() === NULL) {
      $condition->setId(SmartVariationSet::generateUniquePluginId($condition, array_keys($this->getConditions())));
    }
    $this->conditions[$condition->id()] = $condition;

  }

  /**
   * Get Conditions from Variation.
   *
   * This method will automatically load instances from settings on
   * initial call.
   *
   * @return \Drupal\smart_content\Condition\ConditionInterface[]
   *   Array of condition instances.
   */
  public function getConditions() {
    if (!isset($this->conditions)) {
      $this->conditions = [];
      foreach ($this->getConditionsFromSettings() as $plugin) {
        $this->addCondition($plugin);
      }
    }
    return $this->conditions;
  }

  /**
   * Get Condition by ID.
   *
   * @param string $id
   *   The condition ID.
   *
   * @return mixed
   *   Returns condition if it exists.
   */
  public function getCondition($id) {
    foreach ($this->getConditions() as $condition) {
      if ($condition->id() == $id) {
        return $condition;
      }
    }
    return NULL;
  }

  /**
   * Removes Condition by ID.
   *
   * @param string $id
   *   The Condition ID.
   */
  public function removeCondition($id) {
    unset($this->conditions[$id]);
  }

  /**
   * Create Condition plugin instances from configuration array.
   *
   * @return \Drupal\smart_content\Condition\ConditionInterface[]
   *   An array of Conditions from configuration.
   */
  protected function getConditionsFromSettings() {
    $plugins = [];
    if (!empty($this->getConfiguration()['conditions_settings'])) {
      foreach ($this->getConfiguration()['conditions_settings'] as $id => $value) {
        // If condition exists, load it, otherwise fallback to Broken
        // condition handler.
        if ($this->conditionManager->hasDefinition($value['plugin_id'])) {
          $plugins[] = $this->conditionManager->createInstance($value['plugin_id'], $value);
        }
        else {
          $plugins[] = $this->conditionManager->createInstance($this->conditionManager->getFallbackPluginId($value['plugin_id']), $value);
        }
      }
    }
    return $plugins;
  }

  /**
   * Sort Conditions based on weight.
   */
  public function sortConditions() {
    if ($this->getConditions()) {
      uasort($this->conditions, function ($first, $second) {
        return $first->getWeight() > $second->getWeight();
      });
    }
  }

  /**
   * Add Reaction to variation.
   *
   * @param \Drupal\smart_content\Reaction\ReactionInterface $reaction
   *   Reaction plugin.
   */
  public function addReaction(ReactionInterface $reaction) {
    if ($reaction->id() === NULL) {
      $reaction->setId(SmartVariationSet::generateUniquePluginId($reaction, array_keys($this->getReactions())));
    }
    // @todo: find better way to do this.
    $this->reactions[$reaction->id()] = $reaction;

  }

  /**
   * Get Reactions from Variation.
   *
   * This method will automatically load Reactions from configuration if they
   * have not yet been instantiated.
   *
   * @return \Drupal\smart_content\Reaction\ReactionInterface[]
   *   Array of Reaction instances.
   */
  public function getReactions() {
    if (!isset($this->reactions)) {
      $this->reactions = [];
      foreach ($this->getReactionsFromSettings() as $plugin) {
        $this->addReaction($plugin);
      }
    }
    return $this->reactions;
  }

  /**
   * Get Reaction from Variation by id.
   *
   * @param string $id
   *   Reaction id.
   *
   * @return mixed
   *   Returns reaction if it exists.
   */
  public function getReaction($id) {
    foreach ($this->getReactions() as $reaction) {
      if ($reaction->id() == $id) {
        return $reaction;
      }
    }
    return NULL;
  }

  /**
   * Removes Reaction from Variation by id.
   *
   * @param string $id
   *   Reaction id.
   */
  public function removeReaction($id) {
    unset($this->reactions[$id]);
  }

  /**
   * Create Reaction plugin instances from configuration array.
   *
   * @return \Drupal\smart_content\Reaction\ReactionInterface[]
   *   Returns array of Reactions from configuration.
   */
  protected function getReactionsFromSettings() {
    $plugins = [];
    if (!empty($this->getConfiguration()['reactions_settings'])) {
      foreach ($this->getConfiguration()['reactions_settings'] as $id => $value) {
        $plugins[] = $this->reactionManager->createInstance($value['plugin_id'], $value, $this->entity);
      }
    }
    return $plugins;
  }

  /**
   * Sort Reactions by weight.
   */
  public function sortReactions() {
    if ($this->getReactions()) {
      uasort($this->reactions, function ($first, $second) {
        return $first->getWeight() > $second->getWeight();
      });
    }
  }

  /**
   * Writes instantiated objects back to configuration array.
   */
  public function writeChangesToConfiguration() {
    $configuration = $this->getConfiguration();
    $conditions_settings = [];
    foreach ($this->getConditions() as $condition) {
      $condition->writeChangesToConfiguration();
      $conditions_settings[] = $condition->getConfiguration();
    }
    $configuration['conditions_settings'] = $conditions_settings;
    $reactions_settings = [];
    foreach ($this->getReactions() as $reaction) {
      $reaction->writeChangesToConfiguration();
      $reactions_settings[] = $reaction->getConfiguration();
    }
    $configuration['reactions_settings'] = $reactions_settings;
    $this->setConfiguration($configuration);
  }

  /**
   * Return list of Drupal libraries from self and Conditions.
   */
  public function getLibraries() {
    $libraries = [];

    foreach ($this->getConditions() as $condition) {
      $libraries = array_unique(array_merge($libraries, $condition->getLibraries()));
    }

    return $libraries;
  }

  /**
   * Load JS settings from self and Conditions.
   */
  public function getAttachedSettings() {
    $condition_settings = [];
    foreach ($this->getConditions() as $condition) {
      $condition_settings[] = $condition->getAttachedSettings();
    }
    return [
      'id' => $this->id(),
      'conditions' => $condition_settings,
    ];
  }

  /**
   * Load response from Reactions.
   */
  public function getResponse($context = []) {
    $response = new AjaxResponse();
    $content = [];
    foreach ($this->getReactions() as $reaction) {
      $reaction->buildResponse($response);
      if ($reaction_content = $reaction->getResponseContent($context)) {
        $content[] = $reaction_content;
      }
    }
    if (!empty($content)) {
      $response->addCommand(new ReplaceCommand($this->entity->getDecisionAgent()
        ->getResponseTarget(), $content));
    }
    return $response;
  }

  /**
   * Attaches weight to Conditions and sorts.
   */
  public function attachTableConditionWeight($values) {
    foreach ($this->getConditions() as $condition) {
      if (isset($values[$condition->id()]['weight'])) {
        $condition->setWeight($values[$condition->id()]['weight']);
      }
    }
    $this->sortConditions();
  }

  /**
   * Attaches weight to Reactions and sorts.
   */
  public function attachTableReactionWeight($values) {
    foreach ($this->getReactions() as $reaction) {
      if (isset($values[$reaction->id()]['weight'])) {
        $reaction->setWeight($values[$reaction->id()]['weight']);
      }
    }
    $this->sortReactions();
  }

}
