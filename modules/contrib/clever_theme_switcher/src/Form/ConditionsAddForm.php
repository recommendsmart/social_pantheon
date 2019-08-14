<?php

namespace Drupal\clever_theme_switcher\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding a new condition.
 */
class ConditionsAddForm extends ConditionFormBase {

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs a new ConditionAddForm.
   */
  public function __construct(ConditionManager $condition_manager, ContextRepositoryInterface $context_repository) {
    $this->conditionManager = $condition_manager;
    $this->contextRepository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cts_condition_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($plugin_id) {
    // Create a new condition instance.
    return $this->conditionManager->createInstance($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Add condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label condition has been added.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
