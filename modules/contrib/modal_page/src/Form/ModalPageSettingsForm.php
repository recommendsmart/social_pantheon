<?php

namespace Drupal\modal_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Form for configure messages.
 */
class ModalPageSettingsForm extends ConfigFormBase {

  /**
   * Module handler.
   *
   * @var Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * Set Message info.
   */
  public function setMessagesInfo() {

    $type = 'status';

    // Transform to Info if Info Messages is enabled.
    if ($this->moduleHandler->moduleExists('info_messages')) {
      $type = 'info';
    }

    $this->messenger()->addMessage($this->t('You can create your Modal at <a href="@url_settings">@url_settings</a>', [
      '@url_settings' => Url::fromRoute('modal_page.default')->toString(),
    ]), $type);

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_page_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'modal_page.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->setMessagesInfo();
    $config = $this->config('modal_page.settings');

    $form['no_modal_page_external_js'] = [
      '#title' => $this->t("Don't load external JS Bootstrap (bootstrap.min.js)"),
      '#type' => 'checkbox',
      '#description' => $this->t('Just check if the js bootstrap (bootstrap.min.js) is already loaded elsewhere.'),
      '#default_value' => $config->get('no_modal_page_external_js'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $no_modal_page_external_js = $form_state->getValue('no_modal_page_external_js');

    $config = $this->config('modal_page.settings');
    $config->set('no_modal_page_external_js', $no_modal_page_external_js);

    $config->save();

    PhpStorageFactory::get('twig')->deleteAll();

    parent::submitForm($form, $form_state);

  }

}
