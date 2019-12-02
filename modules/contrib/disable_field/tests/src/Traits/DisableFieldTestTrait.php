<?php

namespace Drupal\Tests\disable_field\Traits;

/**
 * Provides methods to simplify checking if a field is disabled or not.
 *
 * This trait is meant to be used only by test classes.
 */
trait DisableFieldTestTrait {

  /**
   * Check if the given field exists, but is not disabled.
   *
   * @param string $field_name
   *   The field name to check.
   */
  protected function checkIfFieldIsNotDisabled(string $field_name) {
    $this->assertSession()->elementExists('css', sprintf('#edit-%s-0-value', str_replace('_', '-', $field_name)));
    $this->assertSession()->elementNotExists('css', sprintf('#edit-%s-0-value[disabled]', str_replace('_', '-', $field_name)));
  }

  /**
   * Check if the given field is disabled.
   *
   * @param string $field_name
   *   The field name to check.
   */
  protected function checkIfFieldIsDisabled(string $field_name) {
    $this->assertSession()->elementExists('css', sprintf('#edit-%s-0-value[disabled]', str_replace('_', '-', $field_name)));
  }

}
