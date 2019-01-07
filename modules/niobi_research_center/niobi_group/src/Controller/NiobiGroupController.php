<?php


namespace Drupal\niobi_group\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Access\AccessResult;

/**
 * Provides the route controller for niobi_group.
 *
 */
class NiobiGroupController extends ControllerBase {

  /**
   * Creates the settings page
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to build the settings page
   * @return $text;
   *   The page text to return
   */
  public function settings_page(GroupInterface $group) {
    return ['#markup' => 'Settings page'];
  }
}
