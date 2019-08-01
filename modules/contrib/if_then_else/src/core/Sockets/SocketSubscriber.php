<?php

namespace Drupal\if_then_else\core\Sockets;

use Drupal\if_then_else\Event\SocketSubscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SocketSubscriber.
 *
 * @package Drupal\if_then_else\core\Sockets
 */
class SocketSubscriber implements EventSubscriberInterface {

  /**
   * Returns a list of events that this class subscribes to.
   */
  public static function getSubscribedEvents() {
    return [
      SocketSubscriptionEvent::EVENT_NAME => 'registerSocket',
    ];
  }

  /**
   * Register different socket types.
   *
   * @param \Drupal\if_then_else\Event\SocketSubscriptionEvent $event
   *   Event.
   */
  public function registerSocket(SocketSubscriptionEvent $event) {
    $event->sockets['form'] = 'Form Object';
    $event->sockets['form_state'] = 'Form State Object';
    $event->sockets['string'] = 'String';
    $event->sockets['string.url'] = 'URL';
    $event->sockets['bool'] = 'Boolean';
    $event->sockets['number'] = 'Number';
    $event->sockets['object.entity.user'] = 'User Object';
    $event->sockets['object.entity.node'] = 'Node';
    $event->sockets['array'] = 'Array';
    $event->sockets['object.entity'] = 'Entity';
    $event->sockets['object.view'] = 'ViewExecutable';
    $event->sockets['field'] = 'Field';
    $event->sockets['object.field.text_with_summary'] = 'Field Text With Summary';
    $event->sockets['object.field.image'] = 'Field Image';
    $event->sockets['object.field.link'] = 'Field Link';
    $event->sockets['object.field.text_long'] = 'Field Text or Text Long';
  }

}
