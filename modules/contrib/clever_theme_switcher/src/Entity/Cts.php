<?php

namespace Drupal\clever_theme_switcher\Entity;

use Drupal\clever_theme_switcher\Entity\Interfaces\CtsInterface;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldException;

/**
 * Defines the Cts entity.
 *
 * @ConfigEntityType(
 *   id = "cts",
 *   label = @Translation("Clever Theme Switcher"),
 *   handlers = {
 *     "list_builder" = "Drupal\clever_theme_switcher\Controller\CtsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\clever_theme_switcher\Form\CtsAddForm",
 *       "edit" = "Drupal\clever_theme_switcher\Form\CtsAddForm",
 *       "delete" = "Drupal\clever_theme_switcher\Form\CtsDeleteForm",
 *       "manage_conditions" = "Drupal\clever_theme_switcher\Form\CtsManageConditionsForm",
 *     }
 *   },
 *   config_prefix = "cts",
 *   admin_permission = "administer themes",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "theme" = "theme",
 *     "pages" = "pages",
 *     "status" = "status",
 *     "condition_collection" = "condition_collection",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "theme",
 *     "pages",
 *     "status",
 *     "condition_collection",
 *   },
 *   links = {
 *     "edit-form" = "/admin/theme/clever_theme_switcher/{cts}",
 *     "delete-form" = "/admin/theme/clever_theme_switcher/{cts}/delete",
 *     "manage-conditions" = "/admin/theme/clever_theme_switcher/{cts}/manage/conditions",
 *   }
 * )
 */
class Cts extends ConfigEntityBase implements CtsInterface {

  /**
   * The maximum length of the field name, in characters.
   */
  const NAME_MAX_LENGTH = 32;

  /**
   * The Cts ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Cts label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Cts theme.
   *
   * @var string
   */
  protected $theme;

  /**
   * The Cts pages.
   *
   * @var string
   */
  protected $pages;

  /**
   * The Cts status.
   *
   * @var bool
   */
  protected $status = FALSE;

  /**
   * The Cts conditions.
   *
   * @var array
   */
  protected $condition_collection = [];

  /**
   * Return the ConditionPluginCollection.
   *
   * @var \Drupal\Core\Condition\ConditionPluginCollection
   */
  protected $plugin_collection;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->get('id');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->get('label');
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->set('label', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    return $this->get('theme');
  }

  /**
   * {@inheritdoc}
   */
  public function setTheme($theme) {
    $this->set('theme', $theme);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPages() {
    return $this->get('pages');
  }

  /**
   * {@inheritdoc}
   */
  public function setPages($pages) {
    $this->set('pages', $pages);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeId() {
    return $this->getEntityType()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getConditionCollection() {
    return $this->get('condition_collection');
  }

  /**
   * {@inheritdoc}
   */
  public function setConditionCollection($condition_collection) {
    $this->set('condition_collection', $condition_collection);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * Returns the conditions.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getConditions() {
    if (!$this->plugin_collection) {
      $this->plugin_collection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->getConditionCollection());
    }
    return $this->plugin_collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'condition_collection' => $this->getConditions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::preSave().
   *
   * @throws \Drupal\Core\Field\FieldException
   *   If the field definition is invalid.
   */
  public function preSave(EntityStorageInterface $storage) {
    if ($this->isNew()) {
      $this->id = $this->getTypeId() . '_' . $this->getId();
    }

    if (mb_strlen($this->getLabel()) > static::NAME_MAX_LENGTH) {
      throw new FieldException('Attempt to create a field storage with an name longer than ' . static::NAME_MAX_LENGTH . ' characters: ' . $this->getLabel());
    }
    parent::preSave($storage);
  }

}
