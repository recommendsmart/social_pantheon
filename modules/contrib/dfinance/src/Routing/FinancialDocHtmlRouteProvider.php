<?php

namespace Drupal\dfinance\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\dfinance\Controller\FinancialDocController;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Financial Document entities.
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class FinancialDocHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($organisation_add_page_route = $this->getAddPageRouteForOrganisation($entity_type)) {
      $collection->add("entity.{$entity_type_id}.add_page_for_organisation", $organisation_add_page_route);
    }

    if ($organisation_add_form_route = $this->getAddFormRouteForOrganisation($entity_type)) {
      $collection->add("entity.{$entity_type_id}.add_form_for_organisation", $organisation_add_form_route);
    }

    if ($organisation_collection_route = $this->getCollectionRouteForOrganisation($entity_type)) {
      $collection->add("entity.{$entity_type_id}.collection_for_organisation", $organisation_collection_route);
    }

    if ($history_route = $this->getHistoryRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.version_history", $history_route);
    }

    if ($revision_route = $this->getRevisionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.revision", $revision_route);
    }

    if ($revert_route = $this->getRevisionRevertRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.revision_revert", $revert_route);
    }

    if ($delete_route = $this->getRevisionDeleteRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.revision_delete", $delete_route);
    }

    if ($translation_route = $this->getRevisionTranslationRevertRoute($entity_type)) {
      $collection->add("{$entity_type_id}.revision_revert_translation_confirm", $translation_route);
    }

    return $collection;
  }

  private function addOrganisationParameterToRoute(Route $route) {
    $parameters = $route->getOption('parameters');

    if ($parameters == null) {
      $parameters = [];
    }

    $route->setOption('parameters', $parameters + [
      'finance_organisation' => ['type' => 'entity:finance_organisation'],
    ]);
  }

  protected function getAddPageRouteForOrganisation(EntityTypeInterface $entity_type) {
    if (!$entity_type->hasLinkTemplate('add-page-for-organisation')) {
      return null;
    }
    if ($route = self::getAddPageRoute($entity_type)) {
      $this->addOrganisationParameterToRoute($route);
      $route->setDefault('_controller', FinancialDocController::class . '::addPageForOrganisation');
      $route->setPath($entity_type->getLinkTemplate('add-page-for-organisation'));
      return $route;
    }
    return null;
  }

  protected function getAddFormRouteForOrganisation(EntityTypeInterface $entity_type) {
    if (!$entity_type->hasLinkTemplate('add-form-for-organisation')) {
      return null;
    }
    if ($route = self::getAddFormRoute($entity_type)) {
      $this->addOrganisationParameterToRoute($route);
      $route->setPath($entity_type->getLinkTemplate('add-form-for-organisation'));
      return $route;
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    return $this->buildCollectionRoute($entity_type, 'collection');
  }

  /**
   * Gets the collection route for organisations.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getCollectionRouteForOrganisation(EntityTypeInterface $entity_type) {
    $route = $this->buildCollectionRoute($entity_type, 'collection-for-organisation');

    if ($route != null) {
      $this->addOrganisationParameterToRoute($route);
    }

    return $route;
  }

  /**
   * Builds the collection route based on which link template is passed
   *
   * @param EntityTypeInterface $entity_type
   * @param string $link_template
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  private function buildCollectionRoute(EntityTypeInterface $entity_type, $link_template) {
    $admin_permission = $entity_type->getAdminPermission();
    if ($admin_permission == NULL || ($link_template != 'collection' && $link_template != 'collection-for-organisation') || !$entity_type->hasLinkTemplate($link_template) || !$entity_type->hasHandlerClass($link_template)) {
      return NULL;
    }

    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
    $label = $entity_type->getCollectionLabel();

    $route = new Route($entity_type->getLinkTemplate($link_template));
    $route->addDefaults([
      '_title' => $label->getUntranslatedString(),
      '_title_arguments' => $label->getArguments(),
      '_title_context' => $label->getOption('context'),
      '_controller' => $entity_type->getHandlerClass($link_template) . '::view',
    ]);
    $route->setRequirement('_permission', $admin_permission); // todo: add per organisation access controls
    return $route;
  }

  /**
   * Gets the version history route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getHistoryRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('version-history')) {
      $route = new Route($entity_type->getLinkTemplate('version-history'));
      $route
        ->setDefaults([
          '_title' => "{$entity_type->getLabel()} revisions",
          '_controller' => '\Drupal\dfinance\Controller\FinancialDocController::revisionOverview',
        ])
        ->setRequirement('_permission', 'access financial document revisions');

      return $route;
    }
  }

  /**
   * Gets the revision route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('revision')) {
      $route = new Route($entity_type->getLinkTemplate('revision'));
      $route
        ->setDefaults([
          '_controller' => '\Drupal\dfinance\Controller\FinancialDocController::revisionShow',
          '_title_callback' => '\Drupal\dfinance\Controller\FinancialDocController::revisionPageTitle',
        ])
        ->setRequirement('_permission', 'access financial document revisions');

      return $route;
    }
  }

  /**
   * Gets the revision revert route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionRevertRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('revision_revert')) {
      $route = new Route($entity_type->getLinkTemplate('revision_revert'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\dfinance\Form\FinancialDocRevisionRevertForm',
          '_title' => 'Revert to earlier revision',
        ])
        ->setRequirement('_permission', 'revert all financial document revisions');

      return $route;
    }
  }

  /**
   * Gets the revision delete route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionDeleteRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('revision_delete')) {
      $route = new Route($entity_type->getLinkTemplate('revision_delete'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\dfinance\Form\FinancialDocRevisionDeleteForm',
          '_title' => 'Delete earlier revision',
        ])
        ->setRequirement('_permission', 'delete all financial document revisions');

      return $route;
    }
  }

  /**
   * Gets the revision translation revert route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getRevisionTranslationRevertRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('translation_revert')) {
      $route = new Route($entity_type->getLinkTemplate('translation_revert'));
      $route
        ->setDefaults([
          '_form' => '\Drupal\dfinance\Form\FinancialDocRevisionRevertTranslationForm',
          '_title' => 'Revert to earlier revision of a translation',
        ])
        ->setRequirement('_permission', 'revert all financial document revisions');

      return $route;
    }
  }

}
