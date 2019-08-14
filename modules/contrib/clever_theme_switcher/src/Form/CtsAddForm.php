<?php

namespace Drupal\clever_theme_switcher\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Form handler for the Cts add and edit forms.
 */
class CtsAddForm extends EntityForm {

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an CtsAddForm object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $current_user) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->getEntity();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $entity->getLabel(),
      '#description' => $this->t("Human name for theme switcher."),
      '#required' => TRUE,
      '#access' => $this->currentUser->hasPermission('administer themes'),
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->getId(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$entity->isNew(),
      '#field_prefix' => ($entity->isNew() ? $entity->getTypeId() . '_' : ''),
      '#access' => $this->currentUser->hasPermission('administer themes'),
    ];

    $themes = \Drupal::service('theme_handler')->listInfo();
    $theme_options = [];

    foreach ($themes as $key => $theme) {
      if (!isset($theme->info['hidden'])) {
        $theme_options[$key] = $theme->info['name'];
      }
    }

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Themes'),
      '#default_value' => $entity->getTheme(),
      '#options' => $theme_options,
      '#required' => TRUE,
      '#access' => $this->currentUser->hasPermission('administer themes'),
    ];
    $form['pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#default_value' => $entity->getPages(),
      '#required' => TRUE,
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is /user/* for every user page. <front> is the front page."),
      '#access' => $this->currentUser->hasPermission('administer themes'),
    ];
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => ($entity->getStatus() ? $entity->getStatus() : FALSE),
      '#access' => $this->currentUser->hasPermission('administer themes'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->entity->isNew()) {
      $actions['submit']['#value'] = $this->t('Save and manage rules');
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $isNew = $this->entity->isNew();
    $status = $entity->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Clever Theme Switcher.', [
        '%label' => $entity->getLabel(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Clever Theme Switcher was not saved.', [
        '%label' => $entity->getLabel(),
      ]), MessengerInterface::TYPE_ERROR);
    }

    if ($isNew) {
      $form_state->setRedirect('entity.cts.manage_conditions', ['cts' => $entity->getId()]);
    }
    else {
      $form_state->setRedirect('entity.cts.list');
    }
  }

  /**
   * Helper function to check whether an Switch Theme.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('cts')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
