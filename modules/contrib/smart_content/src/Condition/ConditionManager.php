<?php

namespace Drupal\smart_content\Condition;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\smart_content\ConditionGroup\ConditionGroupManager;
use Drupal\Component\Plugin\FallbackPluginManagerInterface;

/**
 * Provides the Smart condition plugin manager.
 */
class ConditionManager extends DefaultPluginManager implements FallbackPluginManagerInterface {

  /**
   * ConditionGroupManager instance.
   *
   * @var \Drupal\smart_content\ConditionGroup\ConditionGroupManager
   *   A ConditionGroupManager instance.
   */
  protected $conditionGroupManager;

  /**
   * Constructor for SmartConditionManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\smart_content\ConditionGroup\ConditionGroupManager $condition_group_manager
   *   A ConditionGroupManager instance.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConditionGroupManager $condition_group_manager) {
    parent::__construct('Plugin/smart_content/Condition', $namespaces, $module_handler, 'Drupal\smart_content\Condition\ConditionInterface', 'Drupal\smart_content\Annotation\SmartCondition');
    $this->alterInfo('smart_content_smart_condition_info');
    $this->setCacheBackend($cache_backend, 'smart_content_smart_condition_plugins');
    $this->conditionGroupManager = $condition_group_manager;
  }

  /**
   * Returns a list of Conditions for 'select' elements.
   *
   * Options are sorted by weight and organized into ConditionGroups.
   *
   * @return array
   *   List of options.
   */
  public function getFormOptions() {
    $options = [];
    $condition_group_definitions = $this->conditionGroupManager->getDefinitions();
    $condition_definitions = $this->getDefinitions();

    self::stableSort($condition_group_definitions, function ($first, $second) {
      $first['weight'] = isset($first['weight']) ? $first['weight'] : 0;
      $second['weight'] = isset($second['weight']) ? $second['weight'] : 0;
      return $first['weight'] > $second['weight'];
    });

    self::stableSort($condition_definitions, function ($first, $second) {
      $first['weight'] = isset($first['weight']) ? $first['weight'] : 0;
      $second['weight'] = isset($second['weight']) ? $second['weight'] : 0;
      return $first['weight'] > $second['weight'];
    });

    foreach ($condition_group_definitions as $condition_group_definition) {
      $options[$condition_group_definition['label']->render()] = [];
    }

    foreach ($condition_definitions as $plugin_id => $definition) {
      if ($definition['group'] !== 'hidden') {
        if (isset($condition_group_definitions[$definition['group']])) {
          $label = $condition_group_definitions[$definition['group']]['label'];
          $options[$label->render()][$plugin_id] = $definition['label'];
        }
      }
    }

    return array_filter($options);
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'broken';
  }

  /**
   * Utility function for stable sorting.
   */
  public static function stableSort(array &$array, $cmp_function) {
    if (count($array) < 2) {
      return;
    }
    $halfway = count($array) / 2;
    $array1 = array_slice($array, 0, $halfway, TRUE);
    $array2 = array_slice($array, $halfway, NULL, TRUE);

    self::stableSort($array1, $cmp_function);
    self::stableSort($array2, $cmp_function);
    if (call_user_func($cmp_function, end($array1), reset($array2)) < 1) {
      $array = $array1 + $array2;
      return;
    }
    $array = [];
    reset($array1);
    reset($array2);
    while (current($array1) && current($array2)) {
      if (call_user_func($cmp_function, current($array1), current($array2)) < 1) {
        $array[key($array1)] = current($array1);
        next($array1);
      }
      else {
        $array[key($array2)] = current($array2);
        next($array2);
      }
    }
    while (current($array1)) {
      $array[key($array1)] = current($array1);
      next($array1);
    }
    while (current($array2)) {
      $array[key($array2)] = current($array2);
      next($array2);
    }
  }

}
