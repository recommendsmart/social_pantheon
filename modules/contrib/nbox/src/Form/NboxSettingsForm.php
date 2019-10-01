<?php

namespace Drupal\nbox\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NboxSettingsForm.
 *
 * @ingroup nbox
 */
class NboxSettingsForm extends ConfigFormBase {

  /**
   * Date format.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormat;

  /**
   * NboxSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityStorageInterface $dateFormat
   *   Date format.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityStorageInterface $dateFormat) {
    parent::__construct($config_factory);
    $this->dateFormat = $dateFormat;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'nbox_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nbox.settings'];
  }

  /**
   * Defines the settings form for nbox entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('nbox.settings');
    $form['reply_has_subject'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reply has subject'),
      '#description' => $this->t('If the reply should also have a subject field, if not the thread has a subject based on the first message.'),
      '#default_value' => $config->get('reply_has_subject'),
    ];

    $form['avatar'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Avatar'),
    ];

    $form['avatar']['message_has_avatar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display avatars'),
      '#description' => $this->t('If avatars should be shown in the message thread. The user entity must have the user_picture image field.'),
      '#default_value' => $config->get('message_has_avatar'),
    ];

    $styles = [];
    foreach (ImageStyle::loadMultiple() as $style) {
      $styles[$style->getName()] = $style->label();
    }

    $form['avatar']['message_avatar_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Avatar image style'),
      '#description' => $this->t('The image style to use when displaying avatars in messages.'),
      '#options' => $styles,
      '#default_value' => $config->get('message_avatar_style'),
    ];

    $form['display'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Display'),
    ];

    $formats = $this->dateFormat->loadMultiple();
    $options = [];
    foreach ($formats as $key => $format) {
      $pattern = DateFormat::load($key)->getPattern();
      $options[$key] = $format->label() . " ($pattern)";
    }

    $form['display']['date_today'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format today'),
      '#description' => $this->t('The date format to use when a thread was last updated today.'),
      '#options' => $options,
      '#default_value' => $config->get('date_today'),
    ];

    $form['display']['date_other'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format other'),
      '#description' => $this->t('The date format to use when a thread was last updated not today.'),
      '#options' => $options,
      '#default_value' => $config->get('date_other'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $configStore = $this->config('nbox.settings');

    $configs = [
      'reply_has_subject',
      'message_has_avatar',
      'message_avatar_style',
      'date_today',
      'date_other',
    ];
    foreach ($configs as $config) {
      $configStored = $configStore->get($config);
      $configForm = $form_state->getValue($config);

      if ($configStored !== $configForm) {
        $configStore->set($config, $configForm)->save();
      }
    }

    parent::submitForm($form, $form_state);
  }

}
