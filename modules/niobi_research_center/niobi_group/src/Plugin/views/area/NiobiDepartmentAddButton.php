<?php

namespace Drupal\niobi_group\Plugin\views\area;

use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;

/**
 * Defines a views area plugin.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("niobi_department_add_button")
 */

class NiobiDepartmentAddButton extends AreaPluginBase {
  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE)
  {
    // TODO: Define valid views
    if ($this->view->id() === 'departments') {
      // TODO: Implement Access Check
      $arg = strval(intval(Html::escape($this->view->args[0])));
      $dest = \Drupal::service('path.current')->getPath();
      $urlbase = '/group/add/department?field_organization=' . $arg . '&destination=' . $dest;
      $url = Url::fromUserInput($urlbase);
      $link = Link::fromTextAndUrl(t('Add Department'), $url);
      $link = $link->toRenderable();
      $link['#attributes'] = array('class' => array('btn', 'btn-success'));
      return array(
          '#markup' => render($link),
      );
    }
    else {
      return array(
          '#markup' => '',
      );
    }
  }
}