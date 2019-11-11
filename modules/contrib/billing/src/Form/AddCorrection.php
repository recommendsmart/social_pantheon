<?php

namespace Drupal\billing\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add correction.
 */
class AddCorrection extends FormBase {

  /**
   * Construct
   */
  public function __construct() {
    $this->accountService = \Drupal::service('billing.account');
    $this->currencyService = \Drupal::service('billing.currency');
    $this->transactionService = \Drupal::service('billing.transaction');
  }

  /**
   * F: billingAdd.
   */
  public function billingAdd(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->uid;
    $sum = $form_state->getValue('sum');
    $currency = $this->currencyService->checkCurrency($form_state->getValue('currency'));
    $output = "\n\nbillingAdd:\n";

    if (is_numeric($sum) && is_numeric($uid)) {
      $author = \Drupal::currentUser()->id();
      $comment = "user-$uid correction by uid-$author";
      $transaction = [
        'sum' => floatval($sum),
        'account' => $this->accountService->getUserAccount($uid, $currency),
      ];
      $this->transactionService->deal($transaction, $comment);
      $output .= "$comment\n";
      $sum_human = number_format($sum, 6);
      $currency = $transaction['account']->currency->target_id;
      $output .= "Add account correction for user-$uid ($currency): $sum_human";
    }

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand("#billing", "<pre>$output</pre>"));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $extra = NULL) {
    $form_state->setCached(FALSE);
    $form_state->uid = $extra;
    if (is_numeric($form_state->uid)) {
      $step = '0.01';
      $form["#suffix"] = "<div class='billing-result' id='billing'></div>";
      $form["sum"] = [
        '#type' => 'number',
        '#title' => $this->t('Correction amount'),
        '#description' => "step: $step",
        '#min' => -999999,
        '#max' => 999999,
        '#step' => $step,
      ];
      $form["currency"] = [
        '#type' => 'select',
        '#title' => $this->t('Currency'),
        '#options' => $this->currencyService->formOptions(),
        '#required' => TRUE,
      ];
      $form["billing-submit"] = [
        '#type' => 'submit',
        '#value' => 'Submit',
        '#attributes' => ['class' => ['btn', 'btn-xs', 'btn-primary']],
        '#ajax'   => [
          'callback' => '::billingAdd',
          'effect'   => 'fade',
          'progress' => ['type' => 'throbber', 'message' => "Добавляем"],
        ],
      ];
    }
    return $form;
  }

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

}
