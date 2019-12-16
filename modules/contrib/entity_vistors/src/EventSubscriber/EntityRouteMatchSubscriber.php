<?php


namespace Drupal\entity_visitors\EventSubscriber;

use Drupal\entity_visitors\Service\EntityVisitorsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EntityRouteMatchSubscriber implements EventSubscriberInterface
{

  /**
   * @var EntityVisitorsManager
   */
  private $entityVisitedManager;

  public function __construct(EntityVisitorsManager $entityVisitedManager)
  {
    $this->entityVisitedManager = $entityVisitedManager;
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => 'updateEntityVisitors',
    ];
  }

  /**
   * @param GetResponseEvent $event
   *  Use the get response event to get the current route.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateEntityVisitors(GetResponseEvent $event)
  {
    $routeName = $event->getRequest()->get('_route');
    // match the route with an entity view route.
    if (preg_match('/(entity.)*(.canonical)/', $routeName)) {
      $visitedEntityType = explode('.', $routeName)[1]; // eg, node, user, etc,.
      $visitedEntityId = $event->getRequest()->get($visitedEntityType)->id();

      $this->entityVisitedManager->handleEntity($routeName, $visitedEntityType, $visitedEntityId);
    }
  }
}
