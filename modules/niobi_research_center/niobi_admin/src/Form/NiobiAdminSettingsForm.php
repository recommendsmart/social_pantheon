<?php

namespace Drupal\niobi_admin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\niobi_admin\Utilities\NiobiAccessUtilities;

/**
 * Class GroupSettingsForm.
 */
class NiobiAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'niobi_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['niobi_admin.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('niobi_admin.settings');
    $access_plugins = NiobiAccessUtilities::get_access_plugins();
    $form['access'] = [
      '#type' => 'details',
      '#title' => t('Access Methods'),
      '#open' => TRUE,
    ];
    foreach ($access_plugins as $id => $plugin) {
      $form['access'][$id] = [
        '#type' => 'checkbox',
        '#title' => $plugin['label']->render(),
        '#description' => $plugin['description']->render(),
        '#default_value' => $config->get($id),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('niobi_admin.settings');

    $access_plugins = NiobiAccessUtilities::get_access_plugins();

    foreach ($access_plugins as $id => $plugin) {
      $conf = $config->get($id);
      $form_val = $form_state->getValue($id);

      // Only rebuild the routes if the admin theme switch has changed.
      if ($conf != $form_val) {
        $config->set($id, $form_val)->save();
        \Drupal::service('router.builder')->setRebuildNeeded();
      }
    }

    parent::submitForm($form, $form_state);
  }

}
