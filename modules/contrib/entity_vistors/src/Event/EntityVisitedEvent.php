<?php


namespace Drupal\entity_visitors\Event;


use Symfony\Component\EventDispatcher\Event;

class EntityVisitedEvent extends Event
{

  const VISITED = 'entity_visited.event';

  /**
   * @var $visitedEntityId
   *   The id of the visited entity, like the node id, user id ,etc,.
   */
  public $visitedEntityId;
  /**
   * @var $visitedEntityType
   *   The entity type can be entity_type.bundle_name like node.event, 'node.page' if it has bundles
   *   or only the entity type name like 'user' if it has no bundles.
   */
  public $visitedEntityType;
  /**
   * @var $visitorId
   *  The id of the visitor ($user)
   */
  public $visitorId;

  /**
   * EntityVisitedEvent constructor.
   * @param $visitedEntityId
   * @param $visitedEntityType
   * @param $visitorId
   */
  public function __construct($visitedEntityId, $visitedEntityType, $visitorId)
  {
    $this->visitedEntityId = $visitedEntityId;
    $this->visitedEntityType = $visitedEntityType;
    $this->visitorId = $visitorId;
  }


}
