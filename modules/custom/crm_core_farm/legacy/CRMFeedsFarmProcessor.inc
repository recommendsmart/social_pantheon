<?php

/**
 * @file
 * Class definition of CRMFeedsFarmProcessor.
 */

use Drupal\crm_core_farm\Entity\FarmType;
use Drupal\crm_core_farm\Entity\Farm;

/**
 * Creates farms from feed items.
 */
class CRMFeedsFarmProcessor extends FeedsProcessor {
  /**
   * Define entity type.
   */
  public function entityType() {
    return 'crm_core_farm';
  }

  /**
   * Implements parent::entityInfo().
   */
  protected function entityInfo() {
    $info = parent::entityInfo();
    $info['label plural'] = t('Farms');
    return $info;
  }

  /**
   * Creates a new farm in memory and returns it.
   */
  protected function newEntity(FeedsSource $source) {
    $farm = new stdClass();
    $farm->type = $this->config['farm_type'];
    $farm->changed = REQUEST_TIME;
    $farm->created = REQUEST_TIME;
    $farm->language = LANGUAGE_NONE;
    $farm->revision_log = t('Imported by feeds.');
    $farm->uid = $this->config['author'];
    return $farm;
  }

  /**
   * Loads an existing farm.
   *
   * If the update existing method is not FEEDS_UPDATE_EXISTING, only the farm
   * table will be loaded, foregoing the crm_core_farm_load API for better performance.
   */
  protected function entityLoad(FeedsSource $source, $farm_id) {
    if ($this->config['update_existing'] == FEEDS_UPDATE_EXISTING) {
      $farm = Farm::load($farm_id);
    }
    else {
      // We're replacing the existing farm. Only save the absolutely necessary.
      $farm = db_query("SELECT created, farm_id, vid, type FROM {crm_core_farm} WHERE farm_id = :farm_id", array(':farm_id' => $farm_id))->fetchObject();
      $farm->uid = $this->config['author'];
    }
    // Populate properties that are set by farm_object_prepare().
    if ($this->config['update_existing'] == FEEDS_UPDATE_EXISTING) {
      $farm->revision_log = 'Updated by CRMFeedsFarmProcessor';
    }
    else {
      $farm->revision_log = 'Replaced by CRMFeedsFarmProcessor';
    }
    return $farm;
  }

  /**
   * Save a farm.
   */
  public function entitySave($entity) {
    $entity->save();
  }

  /**
   * Delete a series of farms.
   */
  protected function entityDeleteMultiple($farm_ids) {
    \Drupal::entityTypeManager()->getStorage('crm_core_farm')->delete($farm_ids);
  }

  /**
   * Implement expire().
   *
   * @todo: move to processor stage?
   */
  public function expire($time = NULL) {
    if ($time === NULL) {
      $time = $this->expiryTime();
    }
    if ($time == FEEDS_EXPIRE_NEVER) {
      return;
    }
    $count = $this->getLimit();
    $farms = db_query_range("SELECT c.farm_id FROM {crm_core_farm} c JOIN {feeds_item} fi ON fi.entity_type = 'crm_core_farm' AND c.farm_id = fi.entity_id WHERE fi.id = :id AND c.created < :created", 0, $count, array(':id' => $this->id, ':created' => REQUEST_TIME - $time));
    $farm_ids = array();
    foreach ($farms as $farm) {
      $farm_ids[$farm->farm_id] = $farm->farm_id;
    }
    $this->entityDeleteMultiple($farm_ids);
    if (db_query_range("SELECT 1 FROM {crm_core_farm} c JOIN {feeds_item} fi ON fi.entity_type = 'crm_core_farm' AND c.farm_id = fi.entity_id WHERE fi.id = :id AND c.created < :created", 0, 1, array(':id' => $this->id, ':created' => REQUEST_TIME - $time))->fetchField()) {
      return FEEDS_BATCH_ACTIVE;
    }
    return FEEDS_BATCH_COMPLETE;
  }

  /**
   * Return expiry time.
   */
  public function expiryTime() {
    return $this->config['expire'];
  }

  /**
   * Override parent::configDefaults().
   */
  public function configDefaults() {
    $types = FarmType::getNames();
    $type = key($types);
    return array(
      'farm_type' => $type,
      'expire' => FEEDS_EXPIRE_NEVER,
      'author' => 0,
    ) + parent::configDefaults();
  }

  /**
   * Override parent::configForm().
   */
  public function configForm(&$form_state) {
    $types = FarmType::getNames();
    array_walk($types, 'check_plain');
    $form = parent::configForm($form_state);
    $form['farm_type'] = array(
      '#type' => 'select',
      '#title' => t('Farm type'),
      '#description' => t('Select the type of farms to be created.'),
      '#options' => $types,
      '#default_value' => $this->config['farm_type'],
    );
    $author = user_load($this->config['author']);
    $form['author'] = array(
      '#type' => 'textfield',
      '#title' => t('Author'),
      '#description' => t('Select the author of the farms to be created - leave empty to assign "anonymous".'),
      '#autocomplete_path' => 'user/autocomplete',
      '#default_value' => empty($author->name) ?  'anonymous' : check_plain($author->name),
    );
    $period = drupal_map_assoc(array(FEEDS_EXPIRE_NEVER, 3600, 10800, 21600, 43200, 86400, 259200, 604800, 2592000, 2592000 * 3, 2592000 * 6, 31536000), 'feeds_format_expire');
    $form['expire'] = array(
      '#type' => 'select',
      '#title' => t('Expire farms'),
      '#options' => $period,
      '#description' => t('Select after how much time farms should be deleted. The farm\'s published date will be used for determining the farm\'s age, see Mapping settings.'),
      '#default_value' => $this->config['expire'],
    );
    $form['update_existing']['#options'] = array(
      FEEDS_SKIP_EXISTING => 'Do not update existing farms',
      FEEDS_REPLACE_EXISTING => 'Replace existing farms',
      FEEDS_UPDATE_EXISTING => 'Update existing farms (slower than replacing them)',
    );
    return $form;
  }

  /**
   * Override parent::configFormValidate().
   */
  public function configFormValidate(&$values) {
    if ($author = user_load_by_name($values['author'])) {
      $values['author'] = $author->uid;
    }
    else {
      $values['author'] = 0;
    }
  }

  /**
   * Reschedule if expiry time changes.
   */
  public function configFormSubmit(&$values) {
    if ($this->config['expire'] != $values['expire']) {
      feeds_reschedule($this->id);
    }
    parent::configFormSubmit($values);
  }

  /**
   * Override setTargetElement to operate on a target item that is a farm.
   */
  public function setTargetElement(FeedsSource $source, $target_farm, $target_element, $value) {
    switch ($target_element) {
      case 'created':
        $target_farm->created = feeds_to_unixtime($value, REQUEST_TIME);
        break;
      case 'feeds_source':
        // Get the class of the feed farm importer's fetcher and set the source
        // property. See feeds_farm_update() how $farm->feeds gets stored.
        if ($id = feeds_get_importer_id($this->config['farm_type'])) {
          $class = get_class(feeds_importer($id)->fetcher);
          $target_farm->feeds[$class]['source'] = $value;
          // This effectively suppresses 'import on submission' feature.
          // See feeds_farm_insert().
          $target_farm->feeds['suppress_import'] = TRUE;
        }
        break;
      default:
        parent::setTargetElement($source, $target_farm, $target_element, $value);
        break;
    }
  }

  /**
   * Return available mapping targets.
   */
  public function getMappingTargets() {
    $types = FarmType::loadMultiple();
    $type = $types[$this->config['farm_type']];
    $targets = parent::getMappingTargets();

    $targets += array(
      'farm_id' => array(
        'name' => t('Farm ID'),
        'description' => t('The farm_id of the farm. NOTE: use this feature with care, farm ids are usually assigned by Drupal.'),
        'optional_unique' => TRUE,
      ),
      'uid' => array(
        'name' => t('User ID'),
        'description' => t('The Drupal user ID of the farm author.'),
      ),
      'created' => array(
        'name' => t('Created date'),
        'description' => t('The UNIX time when a farm has been created.'),
      ),
    );

    // If the target content type is a Feed farm, expose its source field.
    if ($id = feeds_get_importer_id($this->config['farm_type'])) {
      $name = feeds_importer($id)->config['name'];
      $targets['feeds_source'] = array(
        'name' => t('Feed source'),
        'description' => t('The farm type created by this processor is a Feed Node, it represents a source itself. Depending on the fetcher selected on the importer "@importer", this field is expected to be for example a URL or a path to a file.', array('@importer' => $name)),
        'optional_unique' => TRUE,
      );
    }

    // Let other modules expose mapping targets.
    self::loadMappers();
    feeds_alter('feeds_processor_targets', $targets, 'crm_core_farm', $this->config['farm_type']);

    return $targets;
  }

  /**
   * Get farm_id of an existing feed item farm if available.
   */
  protected function existingEntityId(FeedsSource $source, FeedsParserResult $result) {
    if ($farm_id = parent::existingEntityId($source, $result)) {
      return $farm_id;
    }

    // Iterate through all unique targets and test whether they do already
    // exist in the database.
    foreach ($this->uniqueTargets($source, $result) as $target => $value) {
      switch ($target) {
        case 'farm_id':
          $farm_id = db_query("SELECT farm_id FROM {crm_core_farm} WHERE farm_id = :farm_id", array(':farm_id' => $value))->fetchField();
          break;
        case 'feeds_source':
          if ($id = feeds_get_importer_id($this->config['farm_type'])) {
            $farm_id = db_query("SELECT fs.feed_farm_id FROM {crm_core_farm} c JOIN {feeds_source} fs ON c.farm_id = fs.feed_farm_id WHERE fs.id = :id AND fs.source = :source", array(':id' => $id, ':source' => $value))->fetchField();
          }
          break;
      }
      if ($farm_id) {
        // Return with the first farm_id found.
        return $farm_id;
      }
    }
    return 0;
  }
}
