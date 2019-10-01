<?php

namespace Drupal\nbox_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\nbox\Entity\Nbox;

/**
 * Provides Nbox route controllers.
 */
class NboxController extends ControllerBase {

  /**
   * Builds an Nbox draft UI.
   *
   * @param \Drupal\nbox\Entity\Nbox $nbox
   *   Nbox.
   *
   * @return array
   *   Render array.
   */
  public function draft(Nbox $nbox) {
    $form = $this->entityFormBuilder()->getForm($nbox);
    return $form;
  }

}
