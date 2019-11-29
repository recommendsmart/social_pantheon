<?php

namespace Drupal\agerp_core_basic\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\agerp_core_basic\BasicTypeInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * AGERP Party Type Entity Class.
 *
 * @ConfigEntityType(
 *   id = "agerp_core_party_type",
 *   label = @Translation("Party type"),
 *   bundle_of = "agerp_core_party",
 *   config_prefix = "type",
 *   handlers = {
 *     "access" = "Drupal\agerp_core_basic\PartyTypeAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\agerp_core_basic\Form\PartyTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\agerp_core_basic\BasicTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer party types",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name",
 *   },
 *   config_export = {
 *     "name",
 *     "type",
 *     "description",
 *     "locked",
 *     "primary_fields",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/agerp_core/party-types/add",
 *     "edit-form" = "/admin/structure/agerp_core/party-types/{agerp_core_party_type}",
 *     "delete-form" = "/admin/structure/agerp_core/party-types/{agerp_core_party_type}/delete",
 *   }
 * )
 */
class PartyType extends ConfigEntityBundleBase implements BasicTypeInterface, EntityDescriptionInterface, RevisionableEntityBundleInterface {

  /**
   * The machine-readable name of this type.
   *
   * @var string
   */
  public $type;

  /**
   * The human-readable name of this type.
   *
   * @var string
   */
  public $name;

  /**
   * A brief description of this type.
   *
   * @var string
   */
  public $description;

  /**
   * Whether or not this type is locked.
   *
   * A boolean indicating whether this type is locked or not, locked individual
   * type cannot be edited or disabled/deleted.
   *
   * @var bool
   */
  public $locked;

  /**
   * Primary fields.
   *
   * An array of key-value pairs, where key is the primary field type and value
   * is real field name used for this type.
   *
   * @var array
   */
  public $primary_fields;

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
    return $this->type;
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
    $party_types = PartyType::loadMultiple();
    $party_types = array_map(function ($party_type) {
      return $party_type->label();
    }, $party_types);
    return $party_types;
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
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * @return array
   */
  public function getPrimaryFields(): array {
    return $this->primary_fields;
  }

  /**
   * @param array $primary_fields
   */
  public function setPrimaryFields(array $primary_fields): void {
    $this->primary_fields = $primary_fields;
  }

}
