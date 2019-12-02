<?php

namespace Drupal\Tests\disable_field\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\disable_field\Traits\DisableFieldTestTrait;
use Drupal\Tests\field_ui\Traits\FieldUiTestTrait;

/**
 * Disable field tests.
 *
 * @group disable_field.
 */
class DisableFieldTest extends BrowserTestBase {

  use FieldUiTestTrait;
  use DisableFieldTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['disable_field', 'block', 'field_ui', 'node'];

  /**
   * The admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Test role 1.
   *
   * @var string
   */
  protected $role1;

  /**
   * Test role 2.
   *
   * @var string
   */
  protected $role2;

  /**
   * Test user 1 with role 1.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user1;

  /**
   * Test user 2 with role 2.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user2;

  /**
   * {@inheritdoc}
   */
  protected static $permissions = [
    'access content',
    'edit any test content',
    'create test content',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('system_breadcrumb_block');

    $this->drupalCreateContentType(['type' => 'test']);

    $this->adminUser = $this->createUser([], NULL, TRUE);
    $this->role1 = $this->drupalCreateRole([
      'access content',
      'edit any test content',
      'create test content',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'disable textfield module',
    ]);
    $this->role2 = $this->drupalCreateRole([
      'access content',
      'edit any test content',
      'create test content',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
    ]);

    $this->user1 = $this->createUser([], NULL, FALSE, ['roles' => [$this->role1]]);
    $this->user2 = $this->createUser([], NULL, FALSE, ['roles' => [$this->role2]]);

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Enable the field for all roles on the content add form.
   */
  public function testDisableFieldOnAddFormEnableForAllRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string');

    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');
  }

  /**
   * Disable the field for all roles on the content add form.
   */
  public function testDisableFieldOnAddFormDisableForAllRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], ['add_disable' => 'all']);

    // Make sure the field is disabled for all roles. Even the admin user.
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');
  }

  /**
   * Disable the field for certain roles on the content add form.
   */
  public function testDisableFieldOnAddFormDisableForCertainRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], [
      'add_disable' => 'roles',
      'add_disable_roles[]' => [$this->role1],
    ]);

    // Make sure the field is disabled for all roles. Even the admin user.
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');
  }

  /**
   * Enable the field for certain roles on the content edit form.
   */
  public function testDisableFieldOnAddFormEnableForCertainRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], [
      'add_disable' => 'roles_enable',
      'add_enable_roles[]' => [$this->role1],
    ]);

    // Make sure the field is disabled for all roles. Even the admin user.
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet('node/add/test');
    $this->checkIfFieldIsDisabled('field_test');
  }

  /**
   * Enable the field for all roles on the content edit form.
   */
  public function testDisableFieldOnEditFormEnableForAllRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string');

    $node = $this->drupalCreateNode(['type' => 'test']);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');
  }

  /**
   * Disable the field for all roles on the content edit form.
   */
  public function testDisableFieldOnEditFormDisableForAllRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], ['edit_disable' => 'all']);

    $node = $this->drupalCreateNode(['type' => 'test']);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');
  }

  /**
   * Disable the field for certain roles on the content edit form.
   */
  public function testDisableFieldOnEditFormDisableForCertainRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], [
      'edit_disable' => 'roles',
      'edit_disable_roles[]' => [$this->role1],
    ]);

    $node = $this->drupalCreateNode(['type' => 'test']);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');
  }

  /**
   * Enable the field for certain roles on the content edit form.
   */
  public function testDisableFieldOnEditFormEnableForCertainRoles(): void {
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], [
      'edit_disable' => 'roles_enable',
      'edit_enable_roles[]' => [$this->role1],
    ]);

    $node = $this->drupalCreateNode(['type' => 'test']);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');

    $this->drupalLogin($this->user1);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');

    $this->drupalLogin($this->user2);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');
  }

  /**
   * Test the permissions provided by the disable_field module.
   */
  public function testDisableFieldSettingsPermission() {
    $assert_session = $this->assertSession();
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string');

    $this->drupalLogin($this->user1);
    $this->drupalGet('/admin/structure/types/manage/test/fields/node.test.field_test');
    $assert_session->elementExists('css', 'select[name="add_disable"]');
    $assert_session->elementExists('css', 'select[name="edit_disable"]');

    $this->drupalLogin($this->user2);
    $this->drupalGet('/admin/structure/types/manage/test/fields/node.test.field_test');
    $assert_session->elementNotExists('css', 'select[name="add_disable"]');
    $assert_session->elementNotExists('css', 'select[name="edit_disable"]');
  }

  /**
   * Test that a disabled field keeps it's value.
   */
  public function testDisableFieldKeepValuesOnDisabledState() {
    $assert_session = $this->assertSession();
    $this->fieldUIAddNewField('admin/structure/types/manage/test', 'test', 'Test field', 'string', [], [
      'edit_disable' => 'roles',
      'edit_disable_roles[]' => [$this->role1],
    ]);
    $node = $this->drupalCreateNode(['type' => 'test']);

    // The admin user can edit the field. Make sure the value is saved.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsNotDisabled('field_test');
    $this->submitForm(['field_test[0][value]' => 'test_value'], 'Save');
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session->elementAttributeContains('css', 'input[name="field_test[0][value]"]', 'value', 'test_value');

    // User 1 cannot edit the field. Make sure the value stays the same.
    $this->drupalLogin($this->user1);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->checkIfFieldIsDisabled('field_test');
    $assert_session->elementAttributeContains('css', 'input[name="field_test[0][value]"]', 'value', 'test_value');
    $this->submitForm([], 'Save');
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session->elementAttributeContains('css', 'input[name="field_test[0][value]"]', 'value', 'test_value');

    // User 1 cannot edit the field. Make sure the value stays the same.
    // Even when the user is tampering with the data.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->submitForm(['field_test[0][value]' => 'new_value'], 'Save');
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session->elementAttributeContains('css', 'input[name="field_test[0][value]"]', 'value', 'test_value');
  }

}
