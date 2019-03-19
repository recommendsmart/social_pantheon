<?php

/**
 * @file
 * Contains \Drupal\redhen_liability\Entity\LiabilityType.
 */

namespace Drupal\redhen_liability\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\redhen_liability\LiabilityTypeInterface;

/**
 * Defines the Liability type entity.
 *
 * @ConfigEntityType(
 *   id = "redhen_liability_type",
 *   label = @Translation("Liability type"),
 *   handlers = {
 *     "list_builder" = "Drupal\redhen_liability\LiabilityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\redhen_liability\Form\LiabilityTypeForm",
 *       "edit" = "Drupal\redhen_liability\Form\LiabilityTypeForm",
 *       "delete" = "Drupal\redhen_liability\Form\LiabilityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\redhen_liability\LiabilityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "redhen_liability_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "redhen_liability",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/redhen/liability_type/{redhen_liability_type}",
 *     "add-form" = "/admin/structure/redhen/liability_type/add",
 *     "edit-form" = "/admin/structure/redhen/liability_type/{redhen_liability_type}/edit",
 *     "delete-form" = "/admin/structure/redhen/liability_type/{redhen_liability_type}/delete",
 *     "collection" = "/admin/structure/redhen/liability_type"
 *   }
 * )
 */
class LiabilityType extends ConfigEntityBundleBase implements LiabilityTypeInterface {
  /**
   * The Liability type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Liability type label.
   *
   * @var string
   */
  protected $label;

}
