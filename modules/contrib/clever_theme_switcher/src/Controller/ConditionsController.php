<?php

namespace Drupal\clever_theme_switcher\Controller;

use Drupal\clever_theme_switcher\Entity\Cts;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConditionsController.
 *
 * @package Drupal\clever_theme_switcher\Controller
 */
class ConditionsController extends ControllerBase {

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConditionManager $conditionManager) {
    $this->conditionManager = $conditionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * Presents a list of conditions to add to the Cts entity.
   *
   * @param \Drupal\di_switchtheme\Entity\Cts $entity
   *   The Cts entity.
   */
  public function list(Cts $entity) {
    $attributes = ['class' => 'bl-links'];

    $build['registered'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Registered'),
      '#open' => TRUE,
      '#weight' => 5,
      '#attributes' => $attributes,
    ];
    $build['registered']['links'] = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $build['registered']['description'] = [
      '#markup' => $this->t('This list of registered modules. It is means that you can use their because this modules have handlers.'),
    ];

    $build['unregistered'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Unregistered'),
      '#open' => TRUE,
      '#weight' => 5,
      '#attributes' => $attributes,
    ];
    $build['unregistered']['links'] = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $build['unregistered']['description'] = [
      '#markup' => $this->t('This list of unregistered modules. You can not use their because they do not have handlers. But if you want to use their you can easily determine some handler for they or you can write your own condition plugin for your needs and to register it.'),
    ];
    $build['#attached']['library'][] = 'clever_theme_switcher/main';

    $plugins = $this->conditionManager->getDefinitions();
    $plugins_registered = \Drupal::request()->attributes->get('_cts_plugin_handler');

    foreach ($plugins as $plugin_id => $plugin) {
      if (array_key_exists($plugin_id, $plugins_registered)) {
        $type = 'registered';
      }
      else {
        $type = 'unregistered';
      }

      $build[$type]['links']['#links'][$plugin_id] = [
        'title' => $plugin['label'],
        'url' => Url::fromRoute('conditions.add', [
          'entity' => $entity->id(),
          'plugin_id' => $plugin_id,
        ]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 'auto',
          ]),
        ],
      ];
    }
    return $build;
  }

}
