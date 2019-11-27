<?php

namespace Drupal\smart_content\Condition;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Smart condition plugins.
 */
abstract class ConditionBase extends PluginBase implements ConditionInterface, ConfigurablePluginInterface {

  /**
   * Configuration storage.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Gets ID for condition.
   *
   * @return string|null
   *   The conditions ID.
   */
  public function id() {
    return isset($this->configuration['id']) ? $this->configuration['id'] : NULL;
  }

  /**
   * Gets condition type ID for JS processing.
   *
   * @return string
   *   A condition type ID.
   */
  public function getTypeId() {
    return 'plugin:' . $this->getPluginId();
  }

  /**
   * Sets ID of condition.
   *
   * @param string $id
   *   Condition id.
   */
  public function setId($id) {
    $configuration = $this->getConfiguration();
    $configuration['id'] = $id;
    $this->setConfiguration($configuration);
  }

  /**
   * Gets weight of condition.
   *
   * @return int
   *   The condition weight.
   */
  public function getWeight() {
    return isset($this->configuration['weight']) ? $this->configuration['weight'] : 0;
  }

  /**
   * Returns array of libraries required to claim and satisfy condition.
   *
   * @return array
   *   An array of Drupal libraries.
   */
  public function getLibraries() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttachedSettings() {
    $definition = $this->getPluginDefinition();
    $settings = [
      'field' => [
        'pluginId' => $this->getPluginId(),
        'type' => $this->getTypeId(),
        'unique' => $definition['unique'],
      ],
    ];
    return $settings;
  }

  /**
   * Utility function to provide "If/If not" select element.
   *
   * @param array $form
   *   Element form.
   * @param array $config
   *   Condition config.
   *
   * @return mixed
   *   Render array with negate Element attached.
   */
  public static function attachNegateElement(array $form, array $config) {
    $form['negate'] = [
      '#title' => 'Negate',
      '#title_display' => 'hidden',
      '#type' => 'select',
      '#default_value' => isset($config['negate']) ? $config['negate'] : FALSE,
      '#empty_option' => 'If',
      '#empty_value' => FALSE,
      '#options' => [TRUE => 'If Not'],
    ];
    return $form;
  }

  /**
   * Sets Weight of condition.
   *
   * @param int $weight
   *   The conditions weight.
   */
  public function setWeight($weight) {
    $configuration = $this->getConfiguration();
    $configuration['weight'] = $weight;
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
      'type' => $this->getTypeId(),
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

}
