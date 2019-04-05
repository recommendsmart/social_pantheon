<?php

namespace Drupal\crm_core_data\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;
use Drupal\crm_core_data\DataTypeInterface;

/**
 * CRM Record Type Entity Class.
 *
 * @ConfigEntityType(
 *   id = "crm_core_record_type",
 *   label = @Translation("CRM Core Record type"),
 *   bundle_of = "crm_core_record",
 *   config_prefix = "record_type",
 *   handlers = {
 *     "access" = "Drupal\crm_core_data\RecordTypeAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\crm_core_data\Form\RecordTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\crm_core_data\RecordTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer record types",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "label",
 *     "id",
 *     "description",
 *     "locked",
 *     "primary_fields",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/crm-core/record-types/add",
 *     "edit-form" = "/admin/structure/crm-core/record-types/{crm_core_record_type}",
 *     "delete-form" = "/admin/structure/crm-core/record-types/{crm_core_record_type}/delete",
 *   }
 * )
 */
class RecordType extends ConfigEntityBundleBase implements DataTypeInterface, EntityDescriptionInterface, RevisionableEntityBundleInterface {

  /**
   * The machine-readable name of this type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of this type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this type.
   *
   * @var string
   */
  protected $description;

  /**
   * Whether or not this type is locked.
   *
   * A boolean indicating whether this type is locked or not, locked data
   * type cannot be edited or disabled/deleted.
   *
   * @var bool
   */
  protected $locked;

  /**
   * Primary fields.
   *
   * An array of key-value pairs, where key is the primary field type and value
   * is real field name used for this type.
   *
   * @var array
   */
  protected $primary_fields;

  /**
   * Should new entities of this bundle have a new revision by default.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    // Ensure default values are set.
    $values += [
      'locked' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getNames() {
    $record_types = RecordType::loadMultiple();
    $record_types = array_map(function ($record_type) {
      return $record_type->label();
    }, $record_types);
    return $record_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * Gets primary fields.
   *
   * @return array
   *   Primary fields array.
   */
  public function getPrimaryFields() {
    return $this->primary_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

}
