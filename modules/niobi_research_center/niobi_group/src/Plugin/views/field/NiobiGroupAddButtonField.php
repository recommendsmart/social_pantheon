<?php


namespace Drupal\niobi_group\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;

/**
 * Defines a views field plugin.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsField("niobi_group_add_button_field")
 */

class NiobiGroupAddButtonField extends FieldPluginBase {
  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }
  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values)
  {
    // TODO: Define valid views
    if ($this->view->id() === 'department_admin') {
      // TODO: Implement Access Check
      $arg = strval(intval(Html::escape($values->_relationship_entities['gid']->id())));
      $dest = \Drupal::service('path.current')->getPath();
      $urlbase = '/group/add/group?field_department=' . $arg . '&destination=' . $dest;
      $url = Url::fromUserInput($urlbase);
      $link = Link::fromTextAndUrl(t('Add Group'), $url);
      $link = $link->toRenderable();
      $link['#attributes'] = array('class' => array('btn', 'btn-xs' ,'btn-success'));
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