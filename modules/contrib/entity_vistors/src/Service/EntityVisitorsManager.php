<?php

namespace Drupal\entity_visitors\Service;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\entity_visitors\Entity\EntityVisitors;
use Drupal\entity_visitors\Event\EntityVisitedEvent;
use Drupal\Core\Config\ConfigManagerInterface;

/**
 * Class EntityVisitorsManager.
 */
class EntityVisitorsManager
{

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * @var AccountProxy
   */
  private $currentUser;
  /**
   * @var RouteMatchInterface
   */
  private $routeMatch;
  /**
   * @var ContainerAwareEventDispatcher
   */
  private $eventDispatcher;
  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;
  /**
   * @var EntityTypeBundleInfo
   */
  private $entityTypeBundleInfo;

  /**
   * Constructs a new EntityVisitorsManager object.
   */
  public function __construct(ConfigManagerInterface $config_manager, AccountProxy $currentUser, RouteMatchInterface $routeMatch,
                              ContainerAwareEventDispatcher $eventDispatcher, EntityTypeManagerInterface $entityTypeManager,
                              EntityTypeBundleInfo $entityTypeBundleInfo)
  {

    $this->configManager = $config_manager;
    $this->currentUser = $currentUser;
    $this->routeMatch = $routeMatch;
    $this->eventDispatcher = $eventDispatcher;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }


  public function handleEntity($routeName, $visitedEntityType, $visitedEntityId)
  {
    $entityVisitor = $this->currentUser;
    $entityVisitorId = $this->currentUser->id();
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($visitedEntityType);
    // if a bundlable entity
    if (sizeof($bundles) > 1 || $visitedEntityType == 'node') {
      // this a bundlable content type. then append the bundle name to the entity name. eg, node.page or node.article.
      // this can be used later in views.
      $bundleName = $this->routeMatch->getParameter($visitedEntityType)->bundle();
      $visitedEntityType .= '.' . $bundleName;
    }
    // handle if user visiting himself.
    if ($visitedEntityType == 'user' && $visitedEntityId === $entityVisitorId) {
      return;
    }
    // excluded roles won't be counted.
    $excludedRoles = $this->configManager->getConfigFactory()->get('entity_visitors.entityvisitiorsconfig')->get('excluded_roles');
    if (isset($excludedRoles) && array_intersect($entityVisitor->getRoles(), $excludedRoles)) {
      return;
    }
    // dispatch event entityVisited to use it for things like creating a message that an entity has reached
    // a certain number views or pretty much anything .
    $entityVisitedEvent = new EntityVisitedEvent($visitedEntityId, $visitedEntityType, $entityVisitorId);
    $this->eventDispatcher->dispatch(EntityVisitedEvent::VISITED, $entityVisitedEvent);

    // load  previous visitors entity that has the same entity.
    $visitedEntityExist = $this->entityTypeManager->getStorage('entity_visitors')->getQuery('AND')
      ->condition('field_visited_entity_id', $visitedEntityId)
      ->condition('name', $visitedEntityType)
      ->execute();
    // this has entity has previously been created.
    if (!empty($visitedEntityExist)) {
      $existedVisitedEntity = EntityVisitors::load(array_values($visitedEntityExist)[0]);
      $existedVisitors = array_map('trim', explode(',', $existedVisitedEntity->field_visited_entity_visitors->getString()));
      if (!in_array($entityVisitorId, $existedVisitors)) {
        $existedVisitedEntity->field_visited_entity_visitors[] = $entityVisitorId;
        $existedVisitedEntity->save();
      }
    } else {
      EntityVisitors::create([
        'field_visited_entity_visitors' => $entityVisitorId,
        'name' => $visitedEntityType,
        'field_visited_entity_id' => $visitedEntityId
      ])->save();
    }
  }
}
