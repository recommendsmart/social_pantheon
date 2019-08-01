<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetCookieAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Class SetCookieAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\SetCookieAction
 */
class SetCookieAction extends Action {

  /**
   * Return node name.
   */
  public static function getName() {
    return 'set_cookie_action';
  }

  /**
   * Event subscriber for register set cookie node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Set Cookie'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetCookieAction\\SetCookieAction',
      'inputs' => [
        'name' => [
          'label' => t('Name'),
          'description' => t('Cookie name.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'value' => [
          'label' => t('Value'),
          'description' => t('Cookie value.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'time_to_expire' => [
          'label' => t('Expires'),
          'description' => t('Number of seconds after which the cookie will expire. Defaults to 31536000 seconds, i.e. 1 year.'),
          'sockets' => ['number'],
        ],
      ],
    ];
  }

  /**
   * Process set cookie action node.
   */
  public function process() {
    $time_to_expire = array_key_exists('time_to_expire', $this->inputs) ? $this->inputs['time_to_expire'] : 31536000;
    setrawcookie($this->inputs['name'], rawurlencode($this->inputs['value']), \Drupal::time()
      ->getRequestTime() + $time_to_expire, '/');
  }

}
