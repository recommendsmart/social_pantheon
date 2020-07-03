<?php

namespace Drupal\announcement_modal\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Announcement Settings.
 */
class AnnouncementSettings extends ConfigFormBase {

  /**
   * The config_factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new AnnouncementSettings object.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'announcement.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'announcement_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('announcement.settings');
    $form['banner_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Announcements and Holidays'),
      '#description' => $this->t('Modal banner title for Announcements and Holidays'),
      '#default_value' => $config->get('banner_title'),
      '#required' => TRUE,
    ];
    $form['banner_desc'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Announcements and Holidays Description'),
      '#description' => $this->t('Modal banner Description for Announcements and Holidays'),
      '#default_value' => $config->get('banner_desc.value'),
      '#format' => $config->get('banner_desc.format'),
      '#required' => TRUE,
    ];
    $form['show_banner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Banner'),
      '#description' => $this->t('To enable and disable the modal.'),
      '#default_value' => $config->get('show_banner'),
    ];
    $form['bg_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Background Color'),
      '#description' => $this->t('To add a background color to the modal.'),
      '#default_value' => $config->get('bg_color'),
    ];
    $form['banner_bg'] = [
      '#type' => 'select',
      '#options' => [
        'black-bg' => 'black',
        'white-bg' => 'white',
        'blue-bg' => 'blue',
        'green-bg' => 'green',
        'darkcyan-bg' => 'darkcyan',
        'gray-bg' => 'gray',
        'steelblue-bg' => 'steelblue',
      ],
      '#default_value' => $config->get('banner_bg'),
      '#states' => [
        // Action to take.
        'visible' => [
          ':input[name="bg_color"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['banner_img'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_size' => [25600000],
      ],
      '#title' => $this->t('Banner Background'),
      '#default_value' => $config->get('banner_img'),
      '#description' => $this->t('Upload or select the banner background image.'),
    ];
    $form['from_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('From Date'),
      '#default_value' => new DrupalDateTime($config->get('from_date')),
      '#states' => [
        // Action to take.
        'visible' => [
          ':input[name="show_banner"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
      '#required' => TRUE,
    ];
    $form['to_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('To Date'),
      '#default_value' => new DrupalDateTime($config->get('to_date')),
      '#states' => [
        // Action to take.
        'visible' => [
          ':input[name="show_banner"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    $this->config('announcement.settings')
      ->set('banner_title', $form_state->getValue('banner_title'))
      ->save();
    $this->config('announcement.settings')
      ->set('banner_desc.value', $values['banner_desc']['value'])
      ->save();
    $this->config('announcement.settings')
      ->set('banner_desc.format', $values['banner_desc']['format'])
      ->save();
    $this->config('announcement.settings')
      ->set('bg_color', $form_state->getValue('bg_color'))
      ->save();
    $this->config('announcement.settings')
      ->set('banner_bg', $form_state->getValue('banner_bg'))
      ->save();
    $this->config('announcement.settings')
      ->set('banner_img', $form_state->getValue('banner_img'))
      ->save();
    $this->config('announcement.settings')
      ->set('from_date', (string) $form_state->getValue('from_date'))
      ->save();
    $this->config('announcement.settings')
      ->set('to_date', (string) $form_state->getValue('to_date'))
      ->save();
    $this->config('announcement.settings')
      ->set('show_banner', $form_state->getValue('show_banner'))
      ->save();
  }

}
