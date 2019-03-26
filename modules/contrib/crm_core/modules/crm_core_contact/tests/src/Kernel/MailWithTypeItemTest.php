<?php

namespace Drupal\Tests\crm_core_contact\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Form\FormState;
use Drupal\crm_core_contact\Plugin\Field\FieldType\MailWithTypeItem;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the 'email_with_type' field type.
 *
 * @group crm_core_contact
 */
class MailWithTypeItemTest extends KernelTestBase {

  /**
   * Form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   */
  protected $formDisplay;

  /**
   * View display.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $viewDisplay;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'field',
    'entity_test',
    'crm_core_contact',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');

    // Create a 'mail_with_type' field storage and field for validation.
    FieldStorageConfig::create([
      'field_name' => 'field_mail_with_type',
      'entity_type' => 'entity_test',
      'type' => 'email_with_type',
      'settings' => [
        'email_types' => [
          'home' => 'Home',
          'work' => 'Work',
        ]
      ],
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_mail_with_type',
      'bundle' => 'entity_test',
      'label' => 'Email with type',
    ])->save();

    // Create a form display for the default form mode.
    $this->formDisplay = EntityFormDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ]);
    $this->formDisplay->setComponent(
      'field_mail_with_type',
      ['type' => 'email_with_type']
    )->save();

    // Create view display for the default view mode.
    $this->viewDisplay = EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'content' => [],
    ]);
    $this->viewDisplay->setComponent(
      'field_mail_with_type',
      ['type' => 'email_with_type']
    )->save();
  }

  /**
   * Tests 'mail_with_type' field type.
   */
  public function testMailWithTypeItem() {
    // Verify entity creation.
    $value = 'mail@example.com';
    $type = 'work';

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create();
    $entity->set('field_mail_with_type', ['value' => $value, 'type' => $type]);
    $entity->save();

    // Verify entity has been created properly.
    $entity = $this->container->get('entity_type.manager')
      ->getStorage('entity_test')
      ->load($entity->id());
    $this->assertEquals($value, $entity->field_mail_with_type->value);
    $this->assertEquals($type, $entity->field_mail_with_type->type);

    // Verify changing the type and value.
    $new_type = 'home';
    $new_value = 'test@example.com';
    $entity->field_mail_with_type->type = $new_type;
    $entity->field_mail_with_type->value = $new_value;
    $this->assertEquals($new_type, $entity->field_mail_with_type->type);
    $this->assertEquals($new_value, $entity->field_mail_with_type->value);

    // Load changed entity and assert changed values.
    $entity->save();
    $entity = $this->container->get('entity_type.manager')
      ->getStorage('entity_test')
      ->load($entity->id());
    $this->assertEquals($new_type, $entity->field_mail_with_type->type);
    $this->assertEquals($new_value, $entity->field_mail_with_type->value);

    // Verify form widget.
    $form = [];
    $form_state = new FormState();
    $this->formDisplay->buildForm($entity, $form, $form_state);
    $widget = $form['field_mail_with_type']['widget'];
    $this->assertEquals('Email with type', $widget['#title'], 'Widget title is correct.');
    $this->assertEquals('Type', $widget['type']['#title'], 'Type element title is correct.');
    $this->assertEquals('home', $widget['type']['#default_value'], 'Type default value is correct.');
    $this->assertEquals(['home' => 'Home', 'work' => 'Work'], $widget['type']['#options'], 'Type options are correct.');
    $this->assertEquals('Email', $widget['value']['#title'], 'Email element title is correct.');
    $this->assertEquals('test@example.com', $widget['value']['#default_value'], 'Email default value is correct.');

    // Verify formatter.
    $build = $this->viewDisplay->build($entity);
    $this->container->get('renderer')->renderRoot($build);
    $this->assertEquals('Home: <a href="mailto:test@example.com">test@example.com</a>', $build['field_mail_with_type']['#markup'], 'Email displayed correctly.');

    // Test storage config form validator.
    // Test invalid mail types configuration value.
    $element = [
      '#value' => "Foo\nwork|Work",
      '#field_has_data' => FALSE,
      '#entity_type' => 'entity_test',
      '#field_name' => 'field_mail_with_type',
      '#email_types' => ['home' => 'Home', 'work' => 'Work'],
    ];
    $form_state = $this->getMock('\Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('setError')
      ->with($element, 'Email types list: invalid input.');
    MailWithTypeItem::validateMailTypes($element, $form_state);

    // Test key that is to long.
    $element['#value'] = "homelonglonglonglonglongwaytolong|Home\nwork|Work";
    $form_state = $this->getMock('\Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('setError')
      ->with($element, 'Email types list: each key must be a string at most 32 characters long.');
    MailWithTypeItem::validateMailTypes($element, $form_state);

    // Test key with invalid characters.
    $element['#value'] = "domači|Home\nslužbeni|Work";
    $form_state = $this->getMock('\Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('setError')
      ->with($element, 'Email types list: only international alphanumeric characters are allowed for keys.');
    MailWithTypeItem::validateMailTypes($element, $form_state);

    // Test valid list of mail types.
    $element['#value'] = "home|Home\nwork|Work";
    $element['#field_has_data'] = TRUE;
    $form_state = $this->getMock('\Drupal\Core\Form\FormState');
    $form_state->expects($this->never())
      ->method('setError');
    MailWithTypeItem::validateMailTypes($element, $form_state);

    // Test removal of the key that already exists in the database.
    $element['#value'] = "work|Work";
    $form_state = $this->getMock('\Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('setError')
      ->with($element, 'Email types list: some values are being removed while currently in use.');
    MailWithTypeItem::validateMailTypes($element, $form_state);

    // Test MailWithTypeItem::emailTypesString().
    $definition = $this->getMock('\Drupal\entity_test\FieldStorageDefinition');
    $definition->expects($this->once())
      ->method('getPropertyDefinitions')
      ->will($this->returnValue([]));
    $field_item = new MailWithTypeItem($definition);
    $reflection = new \ReflectionClass('Drupal\crm_core_contact\Plugin\Field\FieldType\MailWithTypeItem');
    $method = $reflection->getMethod('emailTypesString');
    $method->setAccessible(TRUE);
    $this->assertSame("home|Home\nwork|Work", $method->invokeArgs($field_item, [['home' => 'Home', 'work' => 'Work']]));
  }

}
