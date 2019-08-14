<?php

namespace Drupal\clever_theme_switcher\Form;

use Drupal\clever_theme_switcher\Entity\Cts;
use Drupal\clever_theme_switcher\Helper\ConditionsFormHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Condition\ConditionManager;

/**
 * Provides a base form for editing and adding a condition.
 */
abstract class ConditionFormBase extends FormBase {

  use ContextAwarePluginAssignmentTrait;
  use ConditionsFormHelper;

  /**
   * The block_visibility_group entity this condition belongs to.
   *
   * @var \Drupal\clever_theme_switcher\Entity\Cts
   */
  protected $entity;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;

  /**
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * ConditionFormBase constructor.
   */
  public function __construct(ContextRepositoryInterface $contextRepository, ConditionManager $conditionManager) {
    $this->contextRepository = $contextRepository;
    $this->conditionManager = $conditionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('context.repository'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * Prepares the condition used by this form.
   *
   * @param string $plugin_id
   *   Either a condition ID, or the plugin ID used to create a new
   *   condition.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The condition object.
   */
  abstract protected function prepareCondition($plugin_id);

  /**
   * Returns the text to use for the submit button.
   *
   * @return string
   *   The submit button text.
   */
  abstract protected function submitButtonText();

  /**
   * Returns the text to use for the submit message.
   *
   * @return string
   *   The submit message text.
   */
  abstract protected function submitMessageText();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Cts $entity = NULL, $plugin_id = NULL) {
    $this->entity = $entity;
    $this->condition = $this->prepareCondition($plugin_id);
    $form_state->setTemporaryValue('gathered_contexts', $this->contextRepository->getAvailableContexts());

    $form['condition'] = $this->condition->buildConfigurationForm([], $form_state);
    $form['condition']['#tree'] = TRUE;
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->submitButtonText(),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $condition = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->validateConfigurationForm($form, $condition);
    $form_state->setValue('condition', $condition->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $condition = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->submitConfigurationForm($form, $condition);
    $form_state->setValue('condition', $condition->getValues());

    if ($this->condition instanceof ContextAwarePluginInterface) {
      $this->condition->setContextMapping($condition->getValue('context_mapping', []));
    }

    $this->messenger()->addMessage($this->submitMessageText());
    $configuration = $this->condition->getConfiguration();

    if (!isset($configuration['uuid'])) {
      $this->entity->addCondition($configuration);
    }

    $this->entity->save();
    $build = $this->helper([], $this->entity);

    $response->addCommand(new ReplaceCommand('#edit-condition-collection', $build));
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

}
