<?php

namespace Drupal\views_add_button_group\Plugin\views_add_button;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\views_add_button\ViewsAddButtonInterface;

/**
 * @ViewsAddButton(
 *   id = "views_add_button_group_content",
 *   label = @Translation("ViewsAddButtonGroupContent"),
 *   target_entity = "group_content"
 * )
 */
class ViewsAddButtonGroupContent extends PluginBase implements ViewsAddButtonInterface {

  /**
   * @return string
   *   A string description.
   */
  public function description()
  {
    return $this->t('Views Add Button URL Generator for Group Content entities');
  }

  public static function checkAccess($entity_type, $bundle, $context) {
    // TODO add access callback for this entity
    return TRUE;
  }

  public static function get_bundle($bundle_string) {
    $storage_config = \Drupal::configFactory()->getEditable('group.content_type.' . $bundle_string);
    $plugin_id = $storage_config->getOriginal('content_plugin');
    return $plugin_id;
  }

  public static function generate_url($entity_type, $bundle, $options, $context = '') {
    $c = explode(',', $context);
    // We are expecting a bundle of the type group-group_[entity_type]-[entity_type_bundle]
    $b  = explode('-', $bundle);
    $plugin_id = '';
    // For entities with shorter names, it will be of the type group_type-group_entity-group_entity_bundle
    if (count($b) === 3) {
      $plugin_id = implode(':', array($b[1], $b[2]));
    }
    // Memberships are usually of the type [group_type]-group_membership
    elseif (count($b) === 2 && $b[1] === 'group_membership') {
      $plugin_id = 'group_membership';
      /**
       * In the case of group membership, we may add a second context parameter, 'join' , to make a join link.
       * Alternatively, We can use 'leave' to generate a leave link
       * If it is blank, or anything else, we get the default "add a member" form.
       */
      if (isset($c[1])) {
        switch(trim($c[1])) {
          case 'join':
            $url = Url::fromRoute('entity.group.join', array('group' => $c[0]), $options);
            return $url;
            break;
          case 'leave':
            $url = Url::fromRoute('entity.group.leave', array('group' => $c[0]), $options);
            return $url;
            break;
        }
      }
      $url = Url::fromRoute('entity.group_content.add_form', array('group' => $c[0], 'plugin_id' => $plugin_id), $options);
      return $url;
    }
    // For entities with a long name, i.e. group_content_type_12d187f0f3346 , extract the plugin_id
    elseif (count($b) === 1) {
      $plugin_id = ViewsAddButtonGroupContent::get_bundle($b[0]);
    }
    // Create URL from the data above
    if (isset($c[0]) && !empty($c[0]) && $plugin_id) {
      // If we pass "add" with the group context, we can generate the "relate existing entity to group" link, instead of the create link.
      if (isset($c[1]) && trim($c[1]) === 'add') {
        $url = Url::fromRoute('entity.group_content.add_form', array('group' => $c[0], 'plugin_id' => $plugin_id), $options);
        return $url;
      }
      $url = Url::fromRoute('entity.group_content.create_form', array('group' => $c[0], 'plugin_id' => $plugin_id), $options);
      return $url;
    }
  }
}