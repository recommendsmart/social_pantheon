<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseImageFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;

/**
 * Parse Image field value node class.
 */
class ParseImageFieldValueAction extends Action {

  /**
   * Return name of Image field value action node.
   */
  public static function getName() {
    return 'image_field_value_action';
  }

  /**
   * Event subscriber for registering Image field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('Parse Image Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseImageFieldValueAction\\ParseImageFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => t('Image field object'),
          'description' => t('Image field object'),
          'sockets' => ['object.field.image'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'alt' => [
          'label' => t('Image Alt'),
          'description' => t('Image Uri'),
          'socket' => 'string',
        ],
        'fid' => [
          'label' => t('Image Fid'),
          'description' => t('Image Fid'),
          'socket' => 'number',
        ],
        'width' => [
          'label' => t('Image Width'),
          'description' => t('Image width'),
          'socket' => 'number',
        ],
        'height' => [
          'label' => t('Image Height'),
          'description' => t('Image Title'),
          'socket' => 'number',
        ],
        'description' => [
          'label' => t('Image Description'),
          'description' => t('Image Uri'),
          'socket' => 'string',
        ],
        'title' => [
          'label' => t('Image Title'),
          'description' => t('Image Uri'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Image field value action node.
   */
  public function process() {
    $this->outputs['alt'] = $this->inputs['field_value']->alt;
    $this->outputs['fid'] = $this->inputs['field_value']->fids[0];
    $this->outputs['width'] = $this->inputs['field_value']->width;
    $this->outputs['height'] = $this->inputs['field_value']->height;
    $this->outputs['description'] = $this->inputs['field_value']->description;
    $this->outputs['title'] = $this->inputs['field_value']->title;
  }

}
