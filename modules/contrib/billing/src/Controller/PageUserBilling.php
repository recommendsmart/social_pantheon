<?php

namespace Drupal\billing\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * PageUserBilling.
 */
class PageUserBilling extends ControllerBase {

  /**
   * Construct
   */
  public function __construct() {
    $this->accountService = \Drupal::service('billing.account');
  }

  /**
   * Page.
   */
  public function page($user) {
    $uid = $user->id();

    return [
      'info' => ['#markup' => $this->accountService->renderAccountBalance($user)],
      'add' => ['#markup' => "<a href='/user/$uid/billing/add-correction'>Добавить фантиков</a>."],
      'form' => \Drupal::formBuilder()->getForm('Drupal\billing\Form\AddCorrection', $uid),
    ];
  }

}
