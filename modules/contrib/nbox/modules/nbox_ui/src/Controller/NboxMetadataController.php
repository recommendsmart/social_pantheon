<?php

namespace Drupal\nbox_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\nbox\Entity\NboxMetadata;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides Nbox metadata route controllers.
 */
class NboxMetadataController extends ControllerBase {

  /**
   * Toggle the Nbox metadata star.
   *
   * @param \Drupal\nbox\Entity\NboxMetadata $nbox_metadata
   *   The Nbox metadata entity being triggered.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function star(NboxMetadata $nbox_metadata): RedirectResponse {
    $nbox_metadata->toggleStarred();
    $nbox_metadata->save();
    $destination = Url::fromUserInput(\Drupal::destination()->get());
    if ($destination->isRouted()) {
      return $this->redirect($destination->getRouteName());
    }
  }

}
