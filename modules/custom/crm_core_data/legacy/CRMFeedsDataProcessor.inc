<?php

/**
 * @file
 * Class definition of CRMFeedsDataProcessor.
 */

use Drupal\crm_core_data\Entity\DataType;
use Drupal\crm_core_data\Entity\Data;

/**
 * Creates datas from feed items.
 */
class CRMFeedsDataProcessor extends FeedsProcessor {
  /**
   * Define entity type.
   */
  public function entityType() {
    return 'crm_core_data';
  }

  /**
   * Implements parent::entityInfo().
   */
  protected function entityInfo() {
    $info = parent::entityInfo();
    $info['label plural'] = t('Datas');
    return $info;
  }

  /**
   * Creates a new data in memory and returns it.
   */
  protected function newEntity(FeedsSource $source) {
    $data = new stdClass();
    $data->type = $this->config['data_type'];
    $data->changed = REQUEST_TIME;
    $data->created = REQUEST_TIME;
    $data->language = LANGUAGE_NONE;
    $data->revision_log = t('Imported by feeds.');
    $data->uid = $this->config['author'];
    return $data;
  }

  /**
   * Loads an existing data.
   *
   * If the update existing method is not FEEDS_UPDATE_EXISTING, only the data
   * table will be loaded, foregoing the crm_core_data_load API for better performance.
   */
  protected function entityLoad(FeedsSource $source, $data_id) {
    if ($this->config['update_existing'] == FEEDS_UPDATE_EXISTING) {
      $data = Data::load($data_id);
    }
    else {
      // We're replacing the existing data. Only save the absolutely necessary.
      $data = db_query("SELECT created, data_id, vid, type FROM {crm_core_data} WHERE data_id = :data_id", array(':data_id' => $data_id))->fetchObject();
      $data->uid = $this->config['author'];
    }
    // Populate properties that are set by data_object_prepare().
    if ($this->config['update_existing'] == FEEDS_UPDATE_EXISTING) {
      $data->revision_log = 'Updated by CRMFeedsDataProcessor';
    }
    else {
      $data->revision_log = 'Replaced by CRMFeedsDataProcessor';
    }
    return $data;
  }

  /**
   * Save a data.
   */
  public function entitySave($entity) {
    $entity->save();
  }

  /**
   * Delete a series of datas.
   */
  protected function entityDeleteMultiple($data_ids) {
    \Drupal::entityTypeManager()->getStorage('crm_core_data')->delete($data_ids);
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
    $datas = db_query_range("SELECT c.data_id FROM {crm_core_data} c JOIN {feeds_item} fi ON fi.entity_type = 'crm_core_data' AND c.data_id = fi.entity_id WHERE fi.id = :id AND c.created < :created", 0, $count, array(':id' => $this->id, ':created' => REQUEST_TIME - $time));
    $data_ids = array();
    foreach ($datas as $data) {
      $data_ids[$data->data_id] = $data->data_id;
    }
    $this->entityDeleteMultiple($data_ids);
    if (db_query_range("SELECT 1 FROM {crm_core_data} c JOIN {feeds_item} fi ON fi.entity_type = 'crm_core_data' AND c.data_id = fi.entity_id WHERE fi.id = :id AND c.created < :created", 0, 1, array(':id' => $this->id, ':created' => REQUEST_TIME - $time))->fetchField()) {
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
    $types = DataType::getNames();
    $type = key($types);
    return array(
      'data_type' => $type,
      'expire' => FEEDS_EXPIRE_NEVER,
      'author' => 0,
    ) + parent::configDefaults();
  }

  /**
   * Override parent::configForm().
   */
  public function configForm(&$form_state) {
    $types = DataType::getNames();
    array_walk($types, 'check_plain');
    $form = parent::configForm($form_state);
    $form['data_type'] = array(
      '#type' => 'select',
      '#title' => t('Data type'),
      '#description' => t('Select the type of datas to be created.'),
      '#options' => $types,
      '#default_value' => $this->config['data_type'],
    );
    $author = user_load($this->config['author']);
    $form['author'] = array(
      '#type' => 'textfield',
      '#title' => t('Author'),
      '#description' => t('Select the author of the datas to be created - leave empty to assign "anonymous".'),
      '#autocomplete_path' => 'user/autocomplete',
      '#default_value' => empty($author->name) ?  'anonymous' : check_plain($author->name),
    );
    $period = drupal_map_assoc(array(FEEDS_EXPIRE_NEVER, 3600, 10800, 21600, 43200, 86400, 259200, 604800, 2592000, 2592000 * 3, 2592000 * 6, 31536000), 'feeds_format_expire');
    $form['expire'] = array(
      '#type' => 'select',
      '#title' => t('Expire datas'),
      '#options' => $period,
      '#description' => t('Select after how much time datas should be deleted. The data\'s published date will be used for determining the data\'s age, see Mapping settings.'),
      '#default_value' => $this->config['expire'],
    );
    $form['update_existing']['#options'] = array(
      FEEDS_SKIP_EXISTING => 'Do not update existing datas',
      FEEDS_REPLACE_EXISTING => 'Replace existing datas',
      FEEDS_UPDATE_EXISTING => 'Update existing datas (slower than replacing them)',
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
   * Override setTargetElement to operate on a target item that is a data.
   */
  public function setTargetElement(FeedsSource $source, $target_data, $target_element, $value) {
    switch ($target_element) {
      case 'created':
        $target_data->created = feeds_to_unixtime($value, REQUEST_TIME);
        break;
      case 'feeds_source':
        // Get the class of the feed data importer's fetcher and set the source
        // property. See feeds_data_update() how $data->feeds gets stored.
        if ($id = feeds_get_importer_id($this->config['data_type'])) {
          $class = get_class(feeds_importer($id)->fetcher);
          $target_data->feeds[$class]['source'] = $value;
          // This effectively suppresses 'import on submission' feature.
          // See feeds_data_insert().
          $target_data->feeds['suppress_import'] = TRUE;
        }
        break;
      default:
        parent::setTargetElement($source, $target_data, $target_element, $value);
        break;
    }
  }

  /**
   * Return available mapping targets.
   */
  public function getMappingTargets() {
    $types = DataType::loadMultiple();
    $type = $types[$this->config['data_type']];
    $targets = parent::getMappingTargets();

    $targets += array(
      'data_id' => array(
        'name' => t('Data ID'),
        'description' => t('The data_id of the data. NOTE: use this feature with care, data ids are usually assigned by Drupal.'),
        'optional_unique' => TRUE,
      ),
      'uid' => array(
        'name' => t('User ID'),
        'description' => t('The Drupal user ID of the data author.'),
      ),
      'created' => array(
        'name' => t('Created date'),
        'description' => t('The UNIX time when a data has been created.'),
      ),
    );

    // If the target content type is a Feed data, expose its source field.
    if ($id = feeds_get_importer_id($this->config['data_type'])) {
      $name = feeds_importer($id)->config['name'];
      $targets['feeds_source'] = array(
        'name' => t('Feed source'),
        'description' => t('The data type created by this processor is a Feed Node, it represents a source itself. Depending on the fetcher selected on the importer "@importer", this field is expected to be for example a URL or a path to a file.', array('@importer' => $name)),
        'optional_unique' => TRUE,
      );
    }

    // Let other modules expose mapping targets.
    self::loadMappers();
    feeds_alter('feeds_processor_targets', $targets, 'crm_core_data', $this->config['data_type']);

    return $targets;
  }

  /**
   * Get data_id of an existing feed item data if available.
   */
  protected function existingEntityId(FeedsSource $source, FeedsParserResult $result) {
    if ($data_id = parent::existingEntityId($source, $result)) {
      return $data_id;
    }

    // Iterate through all unique targets and test whether they do already
    // exist in the database.
    foreach ($this->uniqueTargets($source, $result) as $target => $value) {
      switch ($target) {
        case 'data_id':
          $data_id = db_query("SELECT data_id FROM {crm_core_data} WHERE data_id = :data_id", array(':data_id' => $value))->fetchField();
          break;
        case 'feeds_source':
          if ($id = feeds_get_importer_id($this->config['data_type'])) {
            $data_id = db_query("SELECT fs.feed_data_id FROM {crm_core_data} c JOIN {feeds_source} fs ON c.data_id = fs.feed_data_id WHERE fs.id = :id AND fs.source = :source", array(':id' => $id, ':source' => $value))->fetchField();
          }
          break;
      }
      if ($data_id) {
        // Return with the first data_id found.
        return $data_id;
      }
    }
    return 0;
  }
}
