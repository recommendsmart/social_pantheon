<?php

namespace Drupal\matrix_field\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Matrix field entity.
 *
 * @ConfigEntityType(
 *   id = "matrix_field",
 *   label = @Translation("Matrix field"),
 *   handlers = {
 *     "form" = {
 *       "delete" = "Drupal\matrix_field\Form\MatrixFieldDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\matrix_field\MatrixFieldHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "matrix_field",
 *   admin_permission = "configure matrix field",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/matrix-field/{matrix_field}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "field_type",
 *     "weight",
 *     "matrices",
 *     "allowed_values",
 *     "parent",
 *     "unit",
 *     "description"
 *   }
 * )
 */
class MatrixField extends ConfigEntityBase implements MatrixFieldInterface {

  /**
   * The Matrix field ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Matrix field label.
   *
   * @var string
   */
  protected $label;

}
