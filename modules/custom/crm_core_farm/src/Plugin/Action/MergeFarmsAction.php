<?php

namespace Drupal\crm_core_farm\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crm_core_activity\Entity\Activity;
use Drupal\crm_core_farm\Entity\Farm;
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
 * Merges 2 or more farms.
 *
 * @Action(
 *   id = "merge_farms_action",
 *   label = @Translation("Merge farms"),
 *   type = "crm_core_farm"
 * )
 */
class MergeFarmsAction extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

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
    $primary_farm = reset($objects);
    foreach ($objects as $cid => $farm) {
      if ($farm->id() == $this->configuration['data']['farm_id']) {
        $primary_farm = $farm;
        unset($objects[$cid]);
        break;
      }
    }
    unset($this->configuration['data']['farm_id']);
    $wrappers = array();
    foreach ($objects as $farm) {
      $wrappers[$farm->id()] = $farm;
    }
    // Updating farm fields from other selected farms.
    foreach ($this->configuration['data'] as $field_name => $farm_id) {
      if ($primary_farm->id() != $farm_id) {
        $primary_farm->set($field_name, $wrappers[key($farm_id)]->get($field_name)->getValue());
      }
    }
    $primary_farm->save();
    foreach (array_keys($wrappers) as $farm_id) {
      // Creating path aliases for farms that will be deleted.
      $this->pathAliasStorage->save('/crm-core/farm/' . $primary_farm->id(), '/crm-core/farm/' . $farm_id);
      if ($this->moduleHandler->moduleExists('crm_core_activity')) {
        // Replacing participant in existing activities.
        $query = $this->entityQuery->get('crm_core_activity');
        $activities = $query->condition('activity_participants.target_id', $farm_id)
          ->condition('activity_participants.target_type', 'crm_core_farm')
          ->execute();
        if (is_array($activities)) {
          foreach (Activity::loadMultiple($activities) as $activity) {
            foreach ($activity->activity_participants as $delta => $participant) {
              if ($participant->target_id == $farm_id) {
                $activity->get('activity_participants')[$delta]->setValue($primary_farm);
              }
            }
            $activity->save();
          }
        }
      }
      if ($this->moduleHandler->moduleExists('relation')) {
        // Replacing existing relations for farms been deleted with new ones.
        $query = $this->entityQuery->get('relation');
        $relations = $query->condition('endpoints.entity_type', 'crm_core_farm', '=')
              ->condition('endpoints.entity_id', $farm_id, '=')
              ->execute();
        foreach ($relations as $relation_info) {
          $endpoints = array(
            array('entity_type' => 'crm_core_farm', 'entity_id' => $primary_farm->id()),
          );
          $relation = Relation::load($relation_info);
          foreach ($relation->endpoints as $endpoint) {
            if ($endpoint->entity_id != $farm_id) {
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
    $singular = '%farms farm merged to %dest.';
    $plural = '%farms farms merged to %dest.';
    $farms_label = array_map(function ($farm) {
      return $farm->label();
    }, $wrappers);
    $message = $this->translationManager->formatPlural($count, $singular, $plural, array(
      '%farms' => implode(', ', $farms_label),
      '%dest' => $primary_farm->label(),
    ));
    $this->entityTypeManager->getStorage('crm_core_farm')->delete($wrappers);
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
    $primary_farm = array_filter($form_state->getValue('table')['farm_id']);
    if (empty($primary_farm)) {
      $form_state->setError($form['table']['farm_id'], $this->t('You must select primary farm in table header!'));
    }
    if (count($primary_farm) > 1) {
      $form_state->setError($form['table']['farm_id'], $this->t('Supplied more than one primary farm!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = array();
    $selected_farms = Farm::loadMultiple($form_state->getValue('selection'));
    $selected_farms_ids = array_map(function($farm) {
      return $farm->id();
    }, $selected_farms);
    // Lets check farms type, it should be unique.
    $farm_types = array_map(function($farm) {
      return $farm->type->target_id;
    }, $selected_farms);
    // All selected farms have same type.
    if (count(array_unique($farm_types)) != 1) {
      drupal_set_message($this->t('You should select farms of one type to be able to merge them!'), 'error');
      $form_state->setRedirect('entity.crm_core_farm.collection');
    }
    else {
      $form['table'] = array(
        '#type' => 'table',
        '#tree' => TRUE,
        '#selected' => $selected_farms_ids,
      );
      // Creating header.
      $header['field_name'] = array('#markup' => $this->t('Field name\\Farm'));
      foreach ($selected_farms as $farm) {
        $header[$farm->farm_id->value] = array(
          '#type' => 'radio',
          '#title' => $farm->label(),
        );
      }
      $form['table']['farm_id'] = $header;
      $field_instances = $this->entityFieldManager->getFieldDefinitions('crm_core_farm', reset($farm_types));
      unset($field_instances['farm_id']);
      foreach ($field_instances as $field_name => $field_instance) {
        $form['table'][$field_name] = array();
        $form['table'][$field_name]['field_name'] = array('#markup' => $field_instance->getLabel());
        foreach ($selected_farms as $farm) {
          $field_value = array('#markup' => '');
          $farm_field_value = $farm->get($field_name);
          if (isset($farm_field_value)) {
            $field_value_render = $farm_field_value->view('full');
            $field_value_rendered = $this->renderer->render($field_value_render);
            // Some fields can provide empty markup.
            if (!empty($field_value_rendered)) {
              $field_value = array(
                '#type' => 'radio',
                '#title' => $field_value_rendered,
              );
            }
          }
          $form['table'][$field_name][$farm->farm_id->value] = $field_value;
        }
      }
    }

    $form['#attached']['library'][] = 'crm_core_farm/drupal.crm_core_farm.merge-farms';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $data = array('farm_id' => array_shift(array_keys(array_filter($form_state->getValue('table')['farm_id']))));
    unset($form_state->getValue('table')['farm_id']);
    foreach ($form_state->getValue('table') as $field_name => $selection) {
      $data[$field_name] = array_shift(array_keys(array_filter($selection)));
    }
    $this->configuration['data'] = array_filter($data);
  }

}
