<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Nbox type entity.
 *
 * @ConfigEntityType(
 *   id = "nbox_type",
 *   label = @Translation("Nbox type"),
 *   label_singular = @Translation("nbox type"),
 *   label_plural = @Translation("nbox types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count nbox type",
 *     plural = "@count nbox types"
 *   ),
 *   label_collection = @Translation("Nbox types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\nbox\Entity\Controller\NboxTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\nbox\Entity\Form\NboxTypeForm",
 *       "edit" = "Drupal\nbox\Entity\Form\NboxTypeForm",
 *       "delete" = "Drupal\nbox\Entity\Form\NboxTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "nbox_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "nbox",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/nbox/types/{nbox_type}",
 *     "add-form" = "/admin/nbox/types/add",
 *     "edit-form" = "/admin/nbox/types/{nbox_type}/edit",
 *     "delete-form" = "/admin/nbox/types/{nbox_type}/delete",
 *     "collection" = "/admin/nbox/types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   }
 * )
 */
class NboxType extends ConfigEntityBundleBase implements NboxTypeInterface {

  /**
   * The nbox type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The nbox type label.
   *
   * @var string
   */
  protected $label;

}
