<?php

namespace Drupal\content_kanban\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\content_kanban\KanbanService;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * KanbanFilterForm class.
 */
class KanbanFilterForm extends FormBase {

  /**
   * The Kanban service.
   *
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  /**
   * An array with the form params.
   *
   * @var array
   */
  protected $formParams = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(KanbanService $kanban_service, EntityTypeManager $entityTypeManager) {
    $this->kanbanService = $kanban_service;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_kanban.kanban_service'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_kanban_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = []) {

    $this->formParams = $params;

    $form_state->setMethod('GET');

    $form['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filters'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    // User ID.
    $form['filters']['filter_uid'] = [
      '#type' => 'select',
      '#title' => $this->t('User'),
      '#description' => $this->t('Filter by User. Only Users with at least one moderated content are listed here.'),
      '#options' => $this->getUserOptions(),
      '#required' => FALSE,
      '#empty_value' => '',
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => self::getUserIdFilter(),
    ];

    // User ID.
    $form['filters']['filter_state'] = [
      '#type' => 'select',
      '#title' => $this->t('States'),
      '#options' => $this->getStateOptions(),
      '#required' => FALSE,
      '#empty_value' => '',
      '#empty_option' => $this->t('All'),
      '#default_value' => self::getStateFilter(),
    ];

    // Actions.
    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];

    // Submit button.
    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $form['filters']['actions']['reset'] = [
      '#markup' => Link::createFromRoute(
        $this->t('Reset'),
        'content_kanban.kanban'
      )->toString(),
    ];

    return $form;
  }

  /**
   * Gets the User options.
   *
   * @return array
   *   Returns an array with the user options if any or an empty array
   *   otherwise.
   */
  protected function getUserOptions() {

    $options = [];

    // Load Content Moderation entities.
    $content_moderation_entities = $this->kanbanService->getEntityContentModerationEntities($this->formParams['workflow_id']);
    foreach ($content_moderation_entities as $content_moderation_entity) {
      // Get the entity id and entity type id.
      $entityId = $content_moderation_entity->content_entity_id->value;
      $entityTypeId = $content_moderation_entity->content_entity_type_id->value;
      // Get the entity keys and the entity loaded.
      try {
        $entityType = $this->entityTypeManager->getStorage($entityTypeId);
        $entityKeyUserId = $entityType->getEntityType()->getKey('uid');
        if ($entity = $entityType->load($entityId)) {
          $userId = $entity->$entityKeyUserId->getValue();
          if ($user_id = $userId[0]['target_id']) {
            if (!array_key_exists($user_id, $options)) {

              // Load user if existing.
              if ($user = $this->entityTypeManager->getStorage('user')->load($user_id)) {
                // Add to options.
                $options[$user_id] = $user->name->value;
              }
            }
          }
        }
      }
      catch (InvalidPluginDefinitionException $e) {
        watchdog_exception('content_kanban', $e);
      }
      catch (PluginNotFoundException $e) {
        watchdog_exception('content_kanban', $e);
      }
    }

    return $options;
  }

  /**
   * Gets the State options.
   *
   * @return array
   *   Returns an array with the state options.
   */
  protected function getStateOptions() {

    $options = [];

    foreach ($this->formParams['states'] as $state_id => $state) {
      $options[$state_id] = $state['label'];
    }

    return $options;
  }

  /**
   * Gets the User ID filter from request.
   *
   * @return int|null
   *   Returns the filter_uid value if it exists, NULL otherwise.
   */
  public static function getUserIdFilter() {

    if (\Drupal::request()->query->has('filter_uid')) {
      return \Drupal::request()->query->getInt('filter_uid');
    }

    return NULL;
  }

  /**
   * Gets the State filter from request.
   *
   * @return int|null
   *   Returns the filter_state value if it exists, NULL otherwise.
   */
  public static function getStateFilter() {

    if (\Drupal::request()->query->has('filter_state')) {
      return \Drupal::request()->query->get('filter_state');
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
