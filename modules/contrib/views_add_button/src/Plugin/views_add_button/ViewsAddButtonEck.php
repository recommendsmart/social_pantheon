<?php

namespace Drupal\views_add_button\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;

/**
 *
 * @ViewsAddButton(
 *   id = "views_add_button_eck",
 *   label = @Translation("ViewsAddButtonEck"),
 *   target_entity = "animal"
 * )
 */
class ViewsAddButtonEck extends PluginBase implements ViewsAddButtonInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Views Add Button URL Generator for Taxonomy Term entities');
  }

  public static function generate_url($entity_type, $bundle, $options, $context = '') {

    // Create URL from the data above
    $url = Url::fromRoute('eck_entity.animal', array('animal' => $bundle), $options);

    return $url;
  }

}