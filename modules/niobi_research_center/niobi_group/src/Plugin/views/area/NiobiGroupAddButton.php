<?php
/**
 * Created by PhpStorm.
 * User: laboratory.mike
 * Date: 11/18/17
 * Time: 11:17 PM
 */

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
 * @ViewsArea("niobi_research_center_group_add_button")
 */

class NiobiGroupAddButton extends AreaPluginBase {
  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE)
  {
    // TODO: Define valid views
    if ($this->view->id() === 'groups') {
      // TODO: Implement Access Check
      $arg = strval(intval(Html::escape($this->view->args[0])));
      $dest = \Drupal::service('path.current')->getPath();
      $urlbase = '/group/add/group?field_department=' . $arg . '&destination=' . $dest;
      $url = Url::fromUserInput($urlbase);
      $link = Link::fromTextAndUrl(t('Add Group'), $url);
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