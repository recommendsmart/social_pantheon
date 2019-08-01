<?php

namespace Drupal\if_then_else\core\Nodes\Actions\PageRedirectAction;

use Drupal\Component\Utility\Html;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Page redirection action node class.
 */
class PageRedirectAction extends Action {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'page_redirect_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Page Redirect'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\PageRedirectAction\\PageRedirectAction',
      'library' => 'if_then_else/PageRedirectAction',
      'control_class_name' => 'PageRedirectActionControl',
      'inputs' => [
        'url' => [
          'label' => t('URL'),
          'description' => t('URL, can be absolute or relative, internal or external.'),
          'sockets' => ['string.url', 'string'],
        ],
      ],
    ];
  }

  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!property_exists($data, 'input_selection')) {
      $event->errors[] = t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }

    $inputs = $event->node->inputs;
    if ($data->input_selection == 'value' && (!property_exists($data, 'value') || empty(trim($data->value)))) {
      $event->errors[] = t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->input_selection == 'input' && !sizeof($inputs->url->connections)) {
      $event->errors[] = t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process page redirection node.
   */
  public function process() {
    if ($this->data->input_selection == 'value') {
      $url = Html::escape($this->data->value);
    }
    else {
      $url = $this->inputs['url'];
    }
    $redirect = new RedirectResponse($url);
    $redirect->send();
  }

}
