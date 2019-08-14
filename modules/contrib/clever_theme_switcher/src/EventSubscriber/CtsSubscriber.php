<?php

namespace Drupal\clever_theme_switcher\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Component\Utility\NestedArray;

/**
 * EventSubscriber.
 */
class CtsSubscriber implements EventSubscriberInterface {

  /**
   * Priority.
   */
  const PRIORITY = 9000;

  /**
   * Adds custom attributes to the request object.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel request event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();

    if ($request->attributes->get('_cts_plugin_handler') == NULL) {
      $request->attributes->set('_cts_plugin_handler', []);
    }

    $request->attributes->set('_cts_plugin_handler',
      NestedArray::mergeDeep($request->attributes->get('_cts_plugin_handler'), [
        'mobile_device_detection_condition_plugin' => [
          'configuration' => 'devices',
          'context' => [],
        ],
      ])
    );
  }

  /**
   * Adds custom attributes to the request object.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
   *   The kernel request event.
   */
  public function onKernelController(FilterControllerEvent $event) {
    $request = $event->getRequest();
    $request->attributes->set('_cts_plugin_handler',
      NestedArray::mergeDeep($request->attributes->get('_cts_plugin_handler'), [
        'language' => [
          'configuration' => 'langcodes',
          'context' => [\Drupal::languageManager()->getCurrentLanguage()],
        ],
      ])
    );
    $request->attributes->set('_cts_plugin_handler',
      NestedArray::mergeDeep($request->attributes->get('_cts_plugin_handler'), [
        'user_role' => [
          'configuration' => 'roles',
          'context' => [\Drupal::currentUser()],
          'alias' => 'user',
        ],
      ])
    );

    if (\Drupal::request()->attributes->get('node')) {
      $request->attributes->set('_cts_plugin_handler',
        NestedArray::mergeDeep($request->attributes->get('_cts_plugin_handler'), [
          'node_type' => [
            'configuration' => 'bundles',
            'context' => [\Drupal::request()->attributes->get('node')],
            'alias' => 'node',
          ],
        ])
      );
    }
    else {
      $request->attributes->set('_cts_plugin_handler',
        NestedArray::mergeDeep($request->attributes->get('_cts_plugin_handler'), [
          'node_type' => [],
        ])
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', static::PRIORITY];
    $events[KernelEvents::CONTROLLER][] = ['onKernelController'];
    return $events;
  }

}
