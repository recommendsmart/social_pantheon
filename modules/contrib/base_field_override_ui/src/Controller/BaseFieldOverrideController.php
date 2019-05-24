<?php

namespace Drupal\base_field_override_ui\Controller;

use Drupal\Core\Entity\Controller\EntityListController;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Defines a controller to list base field override and create one.
 */
class BaseFieldOverrideController extends EntityListController {

  /**
   * Shows the 'Manage base fields' page.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing($entity_type_id = NULL, $bundle = NULL, RouteMatchInterface $route_match = NULL) {
    return $this->entityManager()->getListBuilder('base_field_override')->render($entity_type_id, $bundle);
  }

  /**
   * Initialize a create form for base field override.
   *
   * @param string $base_field_name
   *   The machine name of the base field.
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle.
   *   The entity bundle.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function add($base_field_name, $entity_type_id = NULL, $bundle = NULL) {
    $fields = $this->entityManager()->getFieldDefinitions($entity_type_id, $bundle);

    if (!isset($fields[$base_field_name])) {
      throw new NotFoundHttpException();
    }

    $config = $fields[$base_field_name]->getConfig($bundle);

    return \Drupal::service('entity.form_builder')->getForm($config, 'edit');
  }


  /**
   * The _title_callback for add a base field override form.
   *
   * @param string $base_field_name
   *   The machine name of the base field.
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle.
   *   The entity bundle.
   *
   * @return string
   *   The label of the field.
   */
  public function getAddTitle($base_field_name, $entity_type_id = NULL, $bundle = NULL) {
    $fields = $this->entityManager()->getFieldDefinitions($entity_type_id, $bundle);

    $config = $fields[$base_field_name]->getConfig($bundle);

    return $this->t('Add @label base field override', ['@label' => $config->label()]);
  }

  /**
   * The _access_callback for add a base field override form.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   * @param string $base_field_name
   *   The machine name of the base field.
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle.
   *   The entity bundle.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function addAccess(AccountInterface $account, $base_field_name, $entity_type_id = NULL, $bundle = NULL) {
    $fields = $this->entityManager()->getFieldDefinitions($entity_type_id, $bundle);

    if (!isset($fields[$base_field_name]) || !$fields[$base_field_name]->isDisplayConfigurable('form')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($account, 'administer ' . $entity_type_id . ' fields');
  }
}
