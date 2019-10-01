<?php

namespace Drupal\nbox\Entity\ViewBuilder;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * View builder handler for Nbox metadata.
 */
class NboxThreadViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);
    /* @var $entity \Drupal\nbox\Entity\NboxThread */
    $build['title'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $entity->getThreadSubject() . '</h2>',
    ];
    $messages = $entity->getMessagesLoaded(FALSE);
    $render_controller = \Drupal::entityTypeManager()->getViewBuilder('nbox');
    foreach ($messages as $key => $message) {
      $build['messages'][$key] = $render_controller->view($message);
    }
  }

}
