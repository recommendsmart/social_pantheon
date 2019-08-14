<?php

namespace Drupal\clever_theme_switcher\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Sets the selected theme on specified pages.
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * Protected currentPath variable.
   *
   * @var Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Protected pathAlias variable.
   *
   * @var Drupal\Core\Path\AliasManagerInterface
   */
  protected $pathAlias;

  /**
   * Protected pathMatcher variable.
   *
   * @var Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Switched Theme.
   *
   * @var string
   */
  private $theme;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request, CurrentPathStack $currentPath, PathMatcherInterface $pathMatcher, AliasManagerInterface $pathAlias) {
    $this->request = $request;
    $this->currentPath = $currentPath;
    $this->pathMatcher = $pathMatcher;
    $this->pathAlias = $pathAlias;
  }

  /**
   * Select specified pages for specified role and apply theme.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match object.
   */
  public function applies(RouteMatchInterface $route_match) {
    $currentRequest = $this->request->getCurrentRequest()->attributes->get("_route");

    if (!$this->pathMatcher->matchPath($currentRequest, 'system*') && !$this->pathMatcher->matchPath($currentRequest, 'admin*')) {
      $storage = \Drupal::entityTypeManager()->getStorage('cts');
      $entity = $storage->loadMultiple();
      $handler = $this->request->getCurrentRequest()->attributes->get("_cts_plugin_handler");

      foreach ($entity as $key => $value) {
        $patterns = $value->getPages();

        if ($value->getStatus() && ($this->pathMatcher->matchPath($this->currentPath->getPath(), $patterns) || $this->pathMatcher->matchPath($this->pathAlias->getAliasByPath($this->currentPath->getPath()), $patterns))) {
          $conditions = $value->getConditions()->getConfiguration();
          $rules = [];

          foreach ($conditions as $condition) {
            $conditionManager = \Drupal::service('plugin.manager.condition');
            $definitions = $conditionManager->getDefinitions();
            $plugin = $conditionManager->createInstance($condition['id']);

            if (isset($handler[$condition['id']]) && !empty($handler[$condition['id']])) {
              $plugin->setConfig($handler[$condition['id']]['configuration'], $condition[$handler[$condition['id']]['configuration']]);
              $plugin->setConfig('negate', $condition['negate']);

              if (isset($handler[$condition['id']]) && !empty($handler[$condition['id']]['context'])) {
                $id = isset($handler[$condition['id']]['alias']) ? $handler[$condition['id']]['alias'] : $condition['id'];

                if (is_array($handler[$condition['id']]['context'])) {
                  foreach ($handler[$condition['id']]['context'] as $context) {
                    $plugin->setContextValue($id, $context);
                  }
                }
                else {
                  $plugin->setContextValue($id, $handler[$condition['id']]['context']);
                }
              }
              $rules[] = $plugin->execute();
            }
            else {
              $rules[] = FALSE;
            }
          }

          if ($rules) {
            if (!in_array(FALSE, $rules)) {
              $this->theme = $value->getTheme();
            }
          }
          break;
        }
      }
    }
    return $this->theme;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->theme;
  }

}
