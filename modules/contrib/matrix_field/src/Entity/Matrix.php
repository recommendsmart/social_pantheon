<?php

namespace Drupal\matrix_field\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Matrix entity.
 *
 * @ConfigEntityType(
 *   id = "matrix_field_matrix",
 *   label = @Translation("Matrix"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\matrix_field\MatrixListBuilder",
 *     "form" = {
 *       "add" = "Drupal\matrix_field\Form\MatrixForm",
 *       "edit" = "Drupal\matrix_field\Form\MatrixForm",
 *       "delete" = "Drupal\matrix_field\Form\MatrixDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\matrix_field\MatrixHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "matrix_field_matrix",
 *   admin_permission = "configure matrix field",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/matrix-field/matrix-field-matrix/{matrix_field_matrix}",
 *     "add-form" = "/admin/structure/matrix-field/matrix-field-matrix/add",
 *     "edit-form" = "/admin/structure/matrix-field/matrix-field-matrix/{matrix_field_matrix}/edit",
 *     "delete-form" = "/admin/structure/matrix-field/matrix-field-matrix/{matrix_field_matrix}/delete",
 *     "collection" = "/admin/structure/matrix-field/matrix-field-matrix"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   }
 * )
 */
class Matrix extends ConfigEntityBase implements MatrixInterface {

  /**
   * The Matrix entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Matrix label.
   *
   * @var string
   */
  protected $label;

}
