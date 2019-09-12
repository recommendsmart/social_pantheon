<?php

namespace Drupal\views_any_route\Plugin\views\area;

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\area\TokenizeAreaPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views_any_route\ViewsAnyRouteUtilities;

/**
 * Defines a views area plugin.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("views_any_route_area")
 */
class ViewsAnyRouteArea extends TokenizeAreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options.
   *
   * @return array
   *   Array of available options for views_any_route form.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['route'] = ['default' => ''];
    $options['route_params'] = ['default' => ''];
    $options['access_plugin'] = ['default' => 'views_any_route_default'];
    $options['url_plugin'] = ['default' => 'views_any_route_default'];
    $options['link_plugin'] = ['default' => 'views_any_route_default'];
    $options['button_text'] = ['default' => ''];
    $options['button_classes'] = ['default' => ''];
    $options['query_string'] = ['default' => ''];
    $options['button_options'] = ['default' => ''];
    $options['button_attributes'] = ['default' => ''];
    $options['button_access_denied'] = ['default' => ['format' => NULL, 'value' => '']];
    $options['button_prefix'] = ['default' => ['format' => NULL, 'value' => '']];
    $options['button_suffix'] = ['default' => ['format' => NULL, 'value' => '']];
    $options['destination'] = ['default' => TRUE];
    $options['tokenize'] = ['default' => FALSE, 'bool' => TRUE];
    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['route'] = [
      '#type' => 'textfield',
      '#title' => t('Route'),
      '#description' => t('Drupal route string.'),
      '#default_value' => $this->options['route'],
      '#weight' => -9,
    ];
    $form['route_params'] = [
      '#type' => 'textarea',
      '#title' => t('Route Parameters'),
      '#description' => t('Drupal route parameters. Enter one parameter per line, in key=value format.'),
      '#default_value' => $this->options['route_params'],
      '#weight' => -9,
    ];
    $form['access_plugin'] = [
      '#type' => 'select',
      '#title' => t('Access Plugin'),
      '#description' => t('The plugin to use for determining access.'),
      '#options' => ViewsAnyRouteUtilities::createPluginList(),
      '#default_value' => $this->options['access_plugin'],
      '#weight' => -10,
      '#required' => TRUE,
    ];
    $form['url_plugin'] = [
      '#type' => 'select',
      '#title' => t('URL Plugin'),
      '#description' => t('The plugin to use for building the URL.'),
      '#options' => ViewsAnyRouteUtilities::createPluginList(),
      '#default_value' => $this->options['url_plugin'],
      '#weight' => -10,
      '#required' => TRUE,
    ];
    $form['link_plugin'] = [
      '#type' => 'select',
      '#title' => t('Link Plugin'),
      '#description' => t('The plugin to use for building the Link.'),
      '#options' => ViewsAnyRouteUtilities::createPluginList(),
      '#default_value' => $this->options['link_plugin'],
      '#weight' => -10,
      '#required' => TRUE,
    ];
    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => t('Button Text'),
      '#description' => t('The text that will be entered into the button.'),
      '#default_value' => $this->options['button_text'],
      '#weight' => -7,
    ];
    $form['query_string'] = [
      '#type' => 'textfield',
      '#title' => t('Query string to append to the link'),
      '#description' => t('Add the query string, without the "?" .'),
      '#default_value' => $this->options['query_string'],
      '#weight' => -6,
    ];
    $form['button_classes'] = [
      '#type' => 'textfield',
      '#title' => t('Button classes for the link - usually "button" or "btn," with additional styling classes.'),
      '#default_value' => $this->options['button_classes'],
      '#weight' => -5,
    ];
    $form['button_attributes'] = [
      '#type' => 'textarea',
      '#title' => t('Additional Button Attributes'),
      '#description' => t('Add one attribute string per line, without quotes (i.e. name=views_any_route).'),
      '#default_value' => $this->options['button_attributes'],
      '#cols' => 60,
      '#rows' => 2,
      '#weight' => -4,
    ];
    $form['button_access_denied'] = [
      '#type' => 'text_format',
      '#title' => t('Access Denied HTML'),
      '#description' => t('HTML to inject if access is denied.'),
      '#cols' => 60,
      '#rows' => 2,
      '#weight' => -3,
      '#default_value' => $this->options['button_access_denied']['value'],
    ];
    $form['button_prefix'] = [
      '#type' => 'text_format',
      '#title' => t('Prefix HTML'),
      '#description' => t('HTML to inject before the button.'),
      '#cols' => 60,
      '#rows' => 2,
      '#weight' => -3,
      '#default_value' => $this->options['button_prefix']['value'],
    ];
    $form['button_suffix'] = [
      '#type' => 'text_format',
      '#title' => t('Suffix HTML'),
      '#description' => t('HTML to inject after the button.'),
      '#cols' => 60,
      '#rows' => 2,
      '#weight' => -2,
      '#default_value' => $this->options['button_suffix']['value'],
    ];
    $form['destination'] = [
      '#type' => 'checkbox',
      '#title' => t('Include destination parameter?'),
      '#default_value' => $this->options['destination'],
      '#weight' => -1,
    ];
    $this->tokenForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $tokenize = $this->options['tokenize'];
    // Load ViewsAnyRoute plugin definitions.
    $plugin_manager = \Drupal::service('plugin.manager.views_any_route');
    $plugin_definitions = $plugin_manager->getDefinitions();

    $access_plugin = $plugin_definitions[$this->options['access_plugin']]['class'];
    $url_plugin = $plugin_definitions[$this->options['url_plugin']]['class'];
    $link_plugin = $plugin_definitions[$this->options['link_plugin']]['class'];

    // Check route access

    /* @var $access_plugin \Drupal\views_any_route\Plugin\views_any_route\ViewsAnyRouteDefault */
    /* @var $url_plugin \Drupal\views_any_route\Plugin\views_any_route\ViewsAnyRouteDefault */
    /* @var $link_plugin \Drupal\views_any_route\Plugin\views_any_route\ViewsAnyRouteDefault */

    $route = $this->options['route'];
    $params_string = $tokenize ? $this->tokenizeValue($this->options['route_params']) : $this->options['route_params'];
    $route_params = [];

    if (!empty($params_string)) {
      $route_params = ViewsAnyRouteUtilities::parameterStringToArray(PHP_EOL, $params_string);
    }

    if (!empty($route) && $access_plugin::checkAccess($route, $route_params)) {
      // OK, the route exists and we can start building the URL.
      $class_string = $tokenize ? Html::escape($this->tokenizeValue($this->options['button_classes'])) :
        Html::escape($this->options['button_classes']);
      // Also, these are escaped later, so we aren't calling the escape function yet.
      $attrs_string = $tokenize ? $this->tokenizeValue($this->options['attrs_string']) : $this->options['attrs_string'];
      $query_string = $tokenize ? $this->tokenizeValue($this->options['query_string']) : $this->options['query_string'];
      $query_string = str_replace('&amp;', '&', $query_string);

      $query = !empty($query_string) ? ViewsAnyRouteUtilities::parameterStringToArray('&', $query_string) : [];
      $attrs = !empty($attrs_string) ? ViewsAnyRouteUtilities::parameterStringToArray(PHP_EOL, $attrs_string) : [];

      // Create options array.
      $options = [
        'attributes' => $attrs,
      ];
      $options['attributes']['class'] = $class_string;
      $options['query'] = $query;
      if ($this->options['destination']) {
        $destination = Url::fromRoute('<current>');
        $options['query']['destination'] = $destination->toString();
      }

      // Generate url and link.
      $url = $url_plugin::generateUrl($route, $route_params, $options);
      $link_text = $tokenize ? Html::escape($this->tokenizeValue($this->options['button_text'])) :
        Html::escape($this->options['button_text']);
      $link = $link_plugin::generateLink($url, $link_text);

      // Add prefix and suffix
      $l = $link->toRenderable();
      if (isset($this->options['button_prefix']) || isset($this->options['button_suffix'])) {
        if (!empty($this->options['button_prefix']['value'])) {
          $prefix = check_markup($this->options['button_prefix']['value'], $this->options['button_prefix']['format']);
          $prefix = $this->options['tokenize'] ? $this->tokenizeValue($prefix) : $prefix;
          $l['#prefix'] = $prefix;
        }
        if (!empty($this->options['button_suffix']['value'])) {
          $suffix = check_markup($this->options['button_suffix']['value'], $this->options['button_suffix']['format']);
          $suffix = $this->options['tokenize'] ? $this->tokenizeValue($suffix) : $suffix;
          $l['#suffix'] = $suffix;
        }
        return $l;
      }
      return $l;
    }
    else {
      if (isset($this->options['button_access_denied']['value']) && !empty($this->options['button_access_denied']['value'])) {
        $markup = check_markup($this->options['button_access_denied']['value'], $this->options['button_access_denied']['format']);
        $markup = $this->options['tokenize'] ? $this->tokenizeValue($markup) : $markup;

        return ['#markup' => $markup];
      }
      else {
        return ['#markup' => ''];
      }
    }
  }

}
