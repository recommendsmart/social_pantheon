<?php

namespace Drupal\smart_content\ConditionType;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Base class for Smart condition type plugins.
 */
abstract class ConditionTypeBase extends PluginBase implements ConditionTypeInterface, PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Configuration storage.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Returns libraries required for js processing.
   *
   * @return array
   *   Array of Drupal libraries to evaluating condition type.
   */
  public function getLibraries() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   *
   * Because ConditionTypes act as widgets, we are storing the form_state
   * values automatically to configuration.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
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
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
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
  public function getFieldAttachedSettings() {
    return [];
  }

}
