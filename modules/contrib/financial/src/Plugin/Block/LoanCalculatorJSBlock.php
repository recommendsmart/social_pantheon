<?php

/**
 * @file
 * Contains \Drupal\loan_calculator\Plugin\Block\MortgageCalculatorJSBlock.
 */

namespace Drupal\financial\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\financial\Form\LoanCalculatorJSForm;

/**
 * Provides a 'Example: uppercase this please' block.
 *
 * @Block(
 *  id = "loan_calculator_js_block",
 *  admin_label = @Translation("Loan EMI JS")
 * )
 */
class LoanCalculatorJSBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $defaultForm = new LoanCalculatorJSForm();
    $form = \Drupal::formBuilder()->getForm($defaultForm);
    return $form;
  }
}