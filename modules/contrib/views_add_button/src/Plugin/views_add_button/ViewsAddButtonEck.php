<?php

namespace Drupal\views_add_button\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;

/**
 *
 * @ViewsAddButton(
 *   id = "views_add_button_node",
 *   label = @Translation("ViewsAddButtonNode"),
 *   target_entity = "node"
 * )
 */
class ViewsAddButtonNode extends PluginBase implements ViewsAddButtonInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Views Add Button URL Generator for Node entities');
  }

  public static function generate_url($entity_type, $bundle, $options, $context = '') {

    // Create URL from the data above
    $url = Url::fromRoute('animal.add', array('animal_type' => $bundle), $options);

    return $url;
  }

}