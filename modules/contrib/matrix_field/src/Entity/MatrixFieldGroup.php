<?php

namespace Drupal\matrix_field\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Matrix Field Group entity.
 *
 * @ConfigEntityType(
 *   id = "matrix_field_group",
 *   label = @Translation("Matrix Field Group"),
 *   handlers = {
 *     "form" = {
 *       "delete" = "Drupal\matrix_field\Form\MatrixFieldGroupDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\matrix_field\MatrixFieldGroupHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "matrix_field_group",
 *   admin_permission = "configure matrix field",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/matrix-field/matrix-field-group/{matrix_field_group}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight"
 *   }
 * )
 */
class MatrixFieldGroup extends ConfigEntityBase implements MatrixFieldGroupInterface {

  /**
   * The Matrix Field Group ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Matrix Field Group label.
   *
   * @var string
   */
  protected $label;

}
