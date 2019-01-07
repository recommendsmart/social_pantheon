<?php
/**
 * Created by PhpStorm.
 * User: laboratory.mike
 * Date: 11/18/17
 * Time: 11:17 PM
 */

namespace Drupal\niobi_research_center_knowledge_base\Plugin\views\area;

use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;

/**
 * Defines a views area plugin.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("niobi_research_center_kb_add_button")
 */

class NiobiResearchCenterKBAddButton extends AreaPluginBase {
  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE)
  {
    dpm(1);
    if ($this->view->id() === 'group_knowledge_base') {
      // TODO: Implement Access Check
      dpm(1);
      $arg = strval(intval(Html::escape($this->view->args[0])));
      $dest = \Drupal::service('path.current')->getPath();
      $urlbase = '/group/' . $arg . '/content/create/group_node:kb?destination=' . $dest;
      $url = Url::fromUserInput($urlbase);
      $link = Link::fromTextAndUrl(t('Add KB Item'), $url);
      $link = $link->toRenderable();
      $link['#attributes'] = array('class' => array('btn', 'btn-success'));
      return array(
          '#markup' => render($link),
      );
    }
    else {
      return array(
          '#markup' => 'g',
      );
    }
  }
}