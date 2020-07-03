<?php

namespace Drupal\Tests\modal_page\Kernel\Form;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\modal_page\Form\ModalPageSettingsForm;

/**
 * Kernel tests for ModalPageSettingsForm.
 *
 * @group modal_page
 */
class ModalPageSettingsFormTest extends KernelTestBase {

  use StringTranslationTrait;

  /**
   * The Modal Page settings form.
   *
   * @var \Drupal\modal_page\Form\ModalPageSettingsForm
   */
  protected $modalPageSettingsForm;

  /**
   * The Modal Page settings.
   *
   * @var object
   */
  protected $modalPageConfig;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'modal_page',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->modalPageConfig = $this->container->get('config.factory')->get('modal_page_admin_settings.settings');
    $this->modalPageSettingsForm = ModalPageSettingsForm::create($this->container);
    $this->submitForm();
  }

  /**
   * Get form values.
   */
  public function getFormState() {
    // Emulate a form state of a submitted form.
    return (new FormState())->setValues([
      'no_modal_page_external_js' => TRUE,
      'allowed_tags' => "h1,h2,a,b,big,code,del,em,i,ins,pre,q,small,span,strong,sub,sup,tt,ol,ul,li,p,br,img",
    ]);
  }

  /**
   * Test to method ModalPageSettings::getFormId().
   */
  public function testFormId() {
    $this->assertEquals('modal_page_admin_settings', $this->modalPageSettingsForm->getFormId());
  }

  /**
   * Test editable config name.
   */
  public function testEditableConfigName() {
    $method = new \ReflectionMethod(ModalPageSettingsForm::class, 'getEditableConfigNames');
    $method->setAccessible(TRUE);

    $configName = $method->invoke($this->modalPageSettingsForm);
    $this->assertEquals(['modal_page.settings'], $configName);
  }

  /**
   * Test submit form.
   */
  public function submitForm() {
    $formState = $this->getFormState();
    $form = $this->modalPageSettingsForm->buildForm([], $formState);
    $this->modalPageSettingsForm->submitForm($form, $formState);
  }

  /**
   * Test ModalPageSettingsForm::submit.
   */
  public function testSubmit() {
    $noExternalJs = $this->config('modal_page.settings')->get('no_modal_page_external_js');
    $this->assertTrue($noExternalJs);
  }

}
