<?php

namespace Drupal\nbox_ui\Form;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Nbox Thread actions.
 *
 * @ingroup nbox_folders
 */
class ThreadActionForm extends FormBase {

  /**
   * VBO Action manager.
   *
   * @var \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager
   */
  protected $vboActionManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * ThreadActionForm constructor.
   *
   * @param \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionManager $vboActionManager
   *   VBO Action manager.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   Module handler.
   */
  public function __construct(ViewsBulkOperationsActionManager $vboActionManager, ModuleHandler $moduleHandler) {
    $this->vboActionManager = $vboActionManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.views_bulk_operations_action'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nbox_ui_thread_actions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['action_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'action-wrapper',
      ],
    ];

    $actions = ['read_thread', 'unread_thread', 'delete_thread'];
    foreach ($actions as $action) {
      $form['action_wrapper'][$action] = [
        '#type' => 'submit',
        '#value' => $this->vboActionManager->getDefinition($action)['label'],
        '#name' => $action,
      ];
    }

    if ($this->moduleHandler->moduleExists('nbox_folders')) {
      $folderStorage = \Drupal::entityTypeManager()->getStorage('nbox_folder');
      $folders = $folderStorage->loadByUser($this->currentUser()->id());
      if (count($folders) > 0) {
        $options = [];

        $threadMetadata = $form_state->getBuildInfo()['args'][0];
        if ($threadMetadata->get('folder')->target_id !== NULL) {
          $options[0] = '- Remove from folder -';
        }

        foreach ($folders as $folder) {
          $options[$folder->id()] = $folder->label();
        }
        $form['folder_wrapper'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'folder-wrapper',
          ],
        ];

        $form['folder_wrapper']['move_folder_thread'] = [
          '#type' => 'submit',
          '#value' => t('Move to folder'),
          '#name' => 'move_folder_thread',
        ];

        $form['folder_wrapper']['move_folder_thread']['#id'] = 'move_to_folder';
        $form['folder_wrapper']['move_folder_thread']['#weight'] = 30;
        // Get folders per user.
        $form['folder_wrapper']['folder'] = [
          '#type' => 'select',
          '#options' => $options,
          '#wrapper_attributes' => [
            'class' => ['folder-select'],
          ],
          '#title' => t('Folder'),
          '#weight' => 29,
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $threadMetadata = $form_state->getBuildInfo()['args'][0];
    $action_name = $form_state->getTriggeringElement()['#name'];

    /** @var \Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase $action */
    if ($action = $this->vboActionManager->createInstance($action_name)) {
      if ($action_name === 'move_folder_thread') {
        $action->setConfiguration(['folder_destination' => $form_state->getValues()['folder']]);
      }

      if ($action->access($threadMetadata, $this->currentUser())) {
        // This should probably be refactored to not "duplicate" the VBO logic.
        $form_state->setRedirect('view.nbox_mailbox.page_1', [
          'arg_0' => 'inbox',
        ]);
        $message = $action->execute($threadMetadata);
        $this->messenger()->addMessage($message);
      }
    }
  }

}
