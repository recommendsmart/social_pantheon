<?php

namespace Drupal\if_then_else\core\Nodes\Events\CronMaintenanceTaskIsPerformed;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Cron Maintenance Task Is Performed event node class.
 */
class CronMaintenanceTaskIsPerformed extends Event {

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'cron_maintenance_task_is_performed_event';
  }

  /**
   * Event subscriber for Cron Maintenance Task Is Performed event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Cron Run'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\CronMaintenanceTaskIsPerformed\\CronMaintenanceTaskIsPerformed',
    ];
  }

}
