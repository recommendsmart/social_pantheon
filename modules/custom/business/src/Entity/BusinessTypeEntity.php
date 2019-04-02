<?php

namespace Drupal\business\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Business Type entity. A configuration entity used to manage
 * bundles for the Business entity.
 *
 * @ConfigEntityType(
 *   id = "business_type",
 *   label = @Translation("Business Type"),
 *   bundle_of = "business",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "business_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\business\BusinessTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\business\Form\BusinessTypeEntityForm",
 *       "add" = "Drupal\business\Form\BusinessTypeEntityForm",
 *       "edit" = "Drupal\business\Form\BusinessTypeEntityForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer business types",
 *   links = {
 *     "canonical" = "/admin/structure/business_type/{business_type}",
 *     "add-form" = "/admin/structure/business_type/add",
 *     "edit-form" = "/admin/structure/business_type/{business_type}/edit",
 *     "delete-form" = "/admin/structure/business_type/{business_type}/delete",
 *     "collection" = "/admin/structure/business_type",
 *   }
 * )
 */
class BusinessTypeEntity extends ConfigEntityBundleBase implements BusinessTypeEntityInterface {

  /**
   * The machine name of the business type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the business type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the business type.
   *
   * @var string
   */
  protected $description;
  
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

}
