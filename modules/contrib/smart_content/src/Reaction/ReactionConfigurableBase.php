<?php

namespace Drupal\smart_content\Reaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Base configuration Reaction implementation.
 *
 * Implements PluginFormInterface to enforce form building in extending
 * classes.
 */
abstract class ReactionConfigurableBase extends ReactionBase implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function writeChangesToConfiguration() {

  }

}
