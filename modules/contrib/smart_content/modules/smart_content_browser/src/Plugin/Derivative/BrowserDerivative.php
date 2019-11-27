<?php

namespace Drupal\smart_content_browser\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Deriver for BrowserCondition.
 *
 * Provides a deriver for
 * Drupal\smart_content_browser\Plugin\smart_content\Condition\BrowserCondition.
 * Definitions are based on properties available in JS from user's browser.
 */
class BrowserDerivative extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [
      'language' => [
        'label' => 'Language',
        'type' => 'textfield',
      ] + $base_plugin_definition,
      'mobile' => [
        'label' => 'Mobile',
        'type' => 'boolean',
        'weight' => -5,
      ] + $base_plugin_definition,
      'platform_os' => [
        'label' => 'Operating System',
        'type' => 'select',
        'options_callback' => [get_class($this), 'getOsOptions'],
      ] + $base_plugin_definition,
      'cookie' => [
        'label' => 'Cookie',
        'type' => 'key_value',
        'unique' => TRUE,
      ] + $base_plugin_definition,
      'cookie_enabled' => [
        'label' => 'Cookie Enabled',
        'type' => 'boolean',
      ] + $base_plugin_definition,
      'localstorage' => [
        'label' => 'localStorage',
        'type' => 'key_value',
        'unique' => TRUE,
      ] + $base_plugin_definition,
      'width' => [
        'label' => 'Width',
        'type' => 'number',
        'format_options' => [
          'suffix' => 'px',
        ],
      ] + $base_plugin_definition,
      'height' => [
        'label' => 'Height',
        'type' => 'number',
        'format_options' => [
          'suffix' => 'px',
        ],
      ] + $base_plugin_definition,
    ];
    return $this->derivatives;
  }

  /**
   * Returns list of 'Operating Systems' for select element.
   *
   * @return array
   *   Array of Operation Systems.
   */
  public static function getOsOptions() {
    return [
      'android' => t('Android'),
      'chromeos' => t('ChromeOS'),
      'ios' => t('iOS'),
      'linux' => t('Linux'),
      'macosx' => t('Mac OS X'),
      'nintendo' => t('Nintendo'),
      'playstation' => t('PlayStation'),
      'windows' => t('Windows'),
    ];
  }

}
