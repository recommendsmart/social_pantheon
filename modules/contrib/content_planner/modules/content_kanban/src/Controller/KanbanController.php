<?php

namespace Drupal\content_kanban\Controller;

use Drupal\content_kanban\KanbanService;
use Drupal\content_kanban\Component\Kanban;
use Drupal\content_kanban\KanbanWorkflowService;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class KanbanController.
 */
class KanbanController extends ControllerBase {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Kanban Service.
   *
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  /**
   * The Moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Constructs a new KanbanController object.
   */
  public function __construct(
    AccountInterface $current_user,
    KanbanService $kanban_service,
    ModerationInformation $moderation_information
  ) {
    $this->currentUser = $current_user;
    $this->kanbanService = $kanban_service;
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('content_kanban.kanban_service'),
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * Show Kanbans.
   *
   * @return array
   *   A renderable array with the Kanbans.
   *
   * @throws \Exception
   */
  public function showKanbans() {
    $build = [];

    $workflows = Workflow::loadMultiple();

    if (!$workflows) {
      $this->messenger()->addMessage($this->t('There are no Workflows configured yet.'), 'error');
      return [];
    }

    foreach ($workflows as $workflow) {

      if (Kanban::isValidContentModerationWorkflow($workflow)) {

        $kanban = new Kanban(
          $this->currentUser,
          $this->kanbanService,
          $workflow
        );

        $build[] = $kanban->build();
      }

    }

    // If there are no Kanbans, display a message.
    if (!$build) {

      $link = Url::fromRoute('entity.workflow.collection')->toString();

      $message = $this->t('To use Content Kanban, you need to have a valid Content Moderation workflow with at least one Entity Type configured. Please go to the <a href="@link">Workflow</a> configuration.', ['@link' => $link]);
      $this->messenger()->addMessage($message, 'error');
    }

    return $build;
  }

  /**
   * Updates the Workflow state of a given Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The current entity.
   * @param string $state_id
   *   The target state id for the current entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns a JSON response with the result of the update process.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateEntityWorkflowState(ContentEntityInterface $entity, $state_id) {
    $data = [
      'success' => FALSE,
      'message' => NULL,
    ];

    // Check if entity is moderated.
    if (!$this->moderationInformation->isModeratedEntity($entity)) {
      $data['message'] = $this->t('Entity @type with ID @id is not a moderated entity.', ['@id' => $entity->id(), '@type' => $entity->getEntityTypeId()]);
      return new JsonResponse($data);
    }

    // Get Workflow from entity.
    $workflow = $this->moderationInformation->getWorkflowForEntity($entity);
    // If Workflow does not exist.
    if (!$workflow) {
      $data['message'] = $this->t('Workflow not found for Entity @type with ID @id.', ['@id' => $entity->id(), '@type' => $entity->getEntityTypeId()]);
      return new JsonResponse($data);
    }

    // Get Workflow States.
    $workflow_states = KanbanWorkflowService::getWorkflowStates($workflow);
    // Check if state given by request matches any of the Workflow's states.
    if (!array_key_exists($state_id, $workflow_states)) {

      $data['message'] = $this->t(
        'Workflow State @state_id is not a valid state of Workflow @workflow_id.',
        [
          '@state_id' => $state_id,
          '@workflow_id' => $workflow->id(),
        ]
      );
      return new JsonResponse($data);
    }

    // Set new state.
    $entity->moderation_state->value = $state_id;

    // Save.
    if ($entity->save() == SAVED_UPDATED) {
      $data['success'] = TRUE;
      $data['message'] = $this->t(
        'Workflow state of Entity @type with @id has been updated to @state_id',
        [
          '@type' => $entity->getEntityTypeId(),
          '@id' => $entity->id(),
          '@state_id' => $state_id,
        ]
      );
    }

    return new JsonResponse($data);
  }

}
