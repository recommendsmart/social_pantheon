<?php

namespace Drupal\smart_content\DecisionAgent;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Template\Attribute;

/**
 * Base class for Smart decision agent plugins.
 */
abstract class DecisionAgentBase extends PluginBase implements DecisionAgentInterface {

  /**
   * Render array for SmartVariationSet placeholder.
   *
   * @return mixed
   *   Render array.
   */
  public function renderPlaceholder(array $context = []) {
    // @todo: determine if VariationSetTypes should be able to append attributes and how would that work for modules to extend it during processing.
    $attributes = new Attribute(
      [$this->getPluginDefinition()['placeholderAttribute'] => $this->entity->getPlaceholderDecisionId()]
    );
    foreach ($context as $key => $value) {
      $attributes->setAttribute('data-context-' . $key, $value);
    }
    if ($default_variation_id = $this->entity->getDefaultVariation()) {
      $attributes->setAttribute('data-default-id', $default_variation_id);
    }
    $output['placeholder'] = [
      '#markup' => '<div ' . (string) $attributes . '></div>',
    ];
    return $output;
  }

  /**
   * Returns a target ID for response.
   */
  public function getResponseTarget() {
    return '[' . $this->getPluginDefinition()['placeholderAttribute'] . '="' . $this->entity->getPlaceholderDecisionId() . '"]';
  }

}
