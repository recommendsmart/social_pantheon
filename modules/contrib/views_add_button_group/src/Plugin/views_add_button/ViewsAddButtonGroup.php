<?php

namespace Drupal\views_add_button_group\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;

/**
 * @ViewsAddButton(
 *   id = "views_add_button_group",
 *   label = @Translation("ViewsAddButtonGroup"),
 *   target_entity = "group"
 * )
 */
class ViewsAddButtonGroup extends PluginBase implements ViewsAddButtonInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Views Add Button URL Generator for Group entities');
  }

  public static function generateUrl($entity_type, $bundle, $options, $context = '') {

    // Create URL from the data above
    $url = Url::fromRoute('entity.group.add_form', array('group_type' => $bundle), $options);

    return $url;
  }

}