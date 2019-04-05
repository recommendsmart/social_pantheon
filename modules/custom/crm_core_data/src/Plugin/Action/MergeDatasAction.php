<?php

namespace Drupal\crm_core_data\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crm_core_activity\Entity\Activity;
use Drupal\crm_core_data\Entity\Data;
use Drupal\relation\Entity\Relation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Path\AliasStorage;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Merges 2 or more datas.
 *
 * @Action(
 *   id = "merge_datas_action",
 *   label = @Translation("Merge datas"),
 *   type = "crm_core_data"
 * )
 */
class MergeDatasAction extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The path alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorage
   */
  protected $pathAliasStorage;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $moduleHandler;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a EmailAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\AliasStorage $path_alias_storage
   *   The path alias storage.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation_manager
   *   The translation manager.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasStorage $path_alias_storage, ModuleHandler $module_handler, QueryFactory $entity_query, TranslationManager $translation_manager, EntityTypeManager $entity_type_manager, EntityFieldManager $entity_field_manager, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pathAliasStorage = $path_alias_storage;
    $this->moduleHandler = $module_handler;
    $this->entityQuery = $entity_query;
    $this->translationManager = $translation_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('path.alias_storage'),
      $container->get('module_handler'),
      $container->get('entity.query'),
      $container->get('string_translation'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $return_as_object ? AccessResult::allowed() : AccessResult::allowed()->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    $primary_data = reset($objects);
    foreach ($objects as $cid => $data) {
      if ($data->id() == $this->configuration['data']['data_id']) {
        $primary_data = $data;
        unset($objects[$cid]);
        break;
      }
    }
    unset($this->configuration['data']['data_id']);
    $wrappers = array();
    foreach ($objects as $data) {
      $wrappers[$data->id()] = $data;
    }
    // Updating data fields from other selected datas.
    foreach ($this->configuration['data'] as $field_name => $data_id) {
      if ($primary_data->id() != $data_id) {
        $primary_data->set($field_name, $wrappers[key($data_id)]->get($field_name)->getValue());
      }
    }
    $primary_data->save();
    foreach (array_keys($wrappers) as $data_id) {
      // Creating path aliases for datas that will be deleted.
      $this->pathAliasStorage->save('/crm-core/data/' . $primary_data->id(), '/crm-core/data/' . $data_id);
      if ($this->moduleHandler->moduleExists('crm_core_activity')) {
        // Replacing participant in existing activities.
        $query = $this->entityQuery->get('crm_core_activity');
        $activities = $query->condition('activity_participants.target_id', $data_id)
          ->condition('activity_participants.target_type', 'crm_core_data')
          ->execute();
        if (is_array($activities)) {
          foreach (Activity::loadMultiple($activities) as $activity) {
            foreach ($activity->activity_participants as $delta => $participant) {
              if ($participant->target_id == $data_id) {
                $activity->get('activity_participants')[$delta]->setValue($primary_data);
              }
            }
            $activity->save();
          }
        }
      }
      if ($this->moduleHandler->moduleExists('relation')) {
        // Replacing existing relations for datas been deleted with new ones.
        $query = $this->entityQuery->get('relation');
        $relations = $query->condition('endpoints.entity_type', 'crm_core_data', '=')
              ->condition('endpoints.entity_id', $data_id, '=')
              ->execute();
        foreach ($relations as $relation_info) {
          $endpoints = array(
            array('entity_type' => 'crm_core_data', 'entity_id' => $primary_data->id()),
          );
          $relation = Relation::load($relation_info);
          foreach ($relation->endpoints as $endpoint) {
            if ($endpoint->entity_id != $data_id) {
              $endpoints[] = array(
                'entity_type' => $endpoint->entity_type,
                'entity_id' => $endpoint->entity_id,
              );
            }
          }
          $new_relation = Relation::create(['relation_type' => $relation->relation_type->target_id, 'endpoints' => $endpoints]);
          $new_relation->save();
        }
      }
    }
    $count = count($wrappers);
    $singular = '%datas data merged to %dest.';
    $plural = '%datas datas merged to %dest.';
    $datas_label = array_map(function ($data) {
      return $data->label();
    }, $wrappers);
    $message = $this->translationManager->formatPlural($count, $singular, $plural, array(
      '%datas' => implode(', ', $datas_label),
      '%dest' => $primary_data->label(),
    ));
    $this->entityTypeManager->getStorage('crm_core_data')->delete($wrappers);
    drupal_set_message($message);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple(array($object));
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $primary_data = array_filter($form_state->getValue('table')['data_id']);
    if (empty($primary_data)) {
      $form_state->setError($form['table']['data_id'], $this->t('You must select primary data in table header!'));
    }
    if (count($primary_data) > 1) {
      $form_state->setError($form['table']['data_id'], $this->t('Supplied more than one primary data!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = array();
    $selected_datas = Data::loadMultiple($form_state->getValue('selection'));
    $selected_datas_ids = array_map(function($data) {
      return $data->id();
    }, $selected_datas);
    // Lets check datas type, it should be unique.
    $data_types = array_map(function($data) {
      return $data->type->target_id;
    }, $selected_datas);
    // All selected datas have same type.
    if (count(array_unique($data_types)) != 1) {
      drupal_set_message($this->t('You should select datas of one type to be able to merge them!'), 'error');
      $form_state->setRedirect('entity.crm_core_data.collection');
    }
    else {
      $form['table'] = array(
        '#type' => 'table',
        '#tree' => TRUE,
        '#selected' => $selected_datas_ids,
      );
      // Creating header.
      $header['field_name'] = array('#markup' => $this->t('Field name\\Data'));
      foreach ($selected_datas as $data) {
        $header[$data->data_id->value] = array(
          '#type' => 'radio',
          '#title' => $data->label(),
        );
      }
      $form['table']['data_id'] = $header;
      $field_instances = $this->entityFieldManager->getFieldDefinitions('crm_core_data', reset($data_types));
      unset($field_instances['data_id']);
      foreach ($field_instances as $field_name => $field_instance) {
        $form['table'][$field_name] = array();
        $form['table'][$field_name]['field_name'] = array('#markup' => $field_instance->getLabel());
        foreach ($selected_datas as $data) {
          $field_value = array('#markup' => '');
          $data_field_value = $data->get($field_name);
          if (isset($data_field_value)) {
            $field_value_render = $data_field_value->view('full');
            $field_value_rendered = $this->renderer->render($field_value_render);
            // Some fields can provide empty markup.
            if (!empty($field_value_rendered)) {
              $field_value = array(
                '#type' => 'radio',
                '#title' => $field_value_rendered,
              );
            }
          }
          $form['table'][$field_name][$data->data_id->value] = $field_value;
        }
      }
    }

    $form['#attached']['library'][] = 'crm_core_data/drupal.crm_core_data.merge-datas';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $data = array('data_id' => array_shift(array_keys(array_filter($form_state->getValue('table')['data_id']))));
    unset($form_state->getValue('table')['data_id']);
    foreach ($form_state->getValue('table') as $field_name => $selection) {
      $data[$field_name] = array_shift(array_keys(array_filter($selection)));
    }
    $this->configuration['data'] = array_filter($data);
  }

}
