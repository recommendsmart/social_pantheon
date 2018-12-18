<?php

namespace Drupal\views_add_button\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;

/**
 *
 * @ViewsAddButton(
 *   id = "views_add_button_default",
 *   label = @Translation("ViewsAddButtonDefault"),
 *   target_entity = ""
 * )
 */
class ViewsAddButtonDefault extends PluginBase implements ViewsAddButtonInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Default Views Add Button URL Generator for entitites which do not have a dedicated ViewsAddButton plugin');
  }

  public static function generate_url($entity_type, $bundle, $options, $context = '') {
    /**
     * Since the create route is difficult to determine from entity annotations (there is not a standard name for a
     * create/register form), We will make an assumption that a no-bundle entity has the format {entity_type}/add , and
     * bundled entities are of the type {entity_type}/add/{bundle} . Differences are handled in other ViewsAddButton plugins.
     */
    $u = $entity_type === $bundle ? '/' . $entity_type . '/add': '/' . $entity_type . '/add/' . $bundle;

    // Create URL from the data above
    $url = Url::fromUserInput($u, $options);

    return $url;
  }

}