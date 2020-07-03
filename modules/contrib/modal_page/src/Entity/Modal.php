<?php

namespace Drupal\modal_page\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\modal_page\ModalPageInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Component\Utility\Xss;
use Drupal\modal_page\Helper\ModalPageFieldHelper;

/**
 * Defines the Modal entity.
 *
 * @ingroup modal_page
 *
 * @ContentEntityType(
 *   id = "modal_page_modal",
 *   label = @Translation("Modal entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\modal_page\Entity\Controller\ModalListBuilder",
 *     "views_data" = "Drupal\modal_page\Entity\ModalViewsData",
 *     "form" = {
 *       "add" = "Drupal\modal_page\Form\ModalForm",
 *       "edit" = "Drupal\modal_page\Form\ModalForm",
 *       "delete" = "Drupal\modal_page\Form\ModalDeleteForm",
 *       "published" = "Drupal\modal_page\Form\ModalPublishedForm",
 *     },
 *     "access" = "Drupal\modal_page\ModalAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "modal",
 *   admin_permission = "administer modal_page entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/modal_page_modal/{modal_page_modal}",
 *     "edit-form" = "/modal_page_modal/{modal_page_modal}/edit",
 *     "delete-form" = "/modal_page_modal/{modal_page_modal}/delete",
 *     "published-form" = "/modal_page_modal/{modal_page_modal}/published",
 *     "collection" = "/modal_page_modal"
 *   },
 *   field_ui_base_route = "modal_page.settings",
 * )
 */
class Modal extends ContentEntityBase implements ModalPageInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $pages = $this->get('pages')->value;
    $pages = explode(PHP_EOL, $pages);

    foreach ($pages as $page) {
      $path = $page;
      if ($path != '<front>') {
        $path = Xss::filter($path);
      }
      $path = trim($path);
      $aliasPath[] = \Drupal::service('path.alias_manager')->getPathByAlias($path);
    }
    $pages = implode(PHP_EOL, $aliasPath);
    // Set original path from alias in database in pages field.
    $this->set('pages', $pages);
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $modalPageFieldHelper = new ModalPageFieldHelper();

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Modal entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Modal entity.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Body'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 11,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Modal By'))
      ->setRequired(TRUE)
      ->setSettings([
        'allowed_values' => [
          'page' => 'Page',
          'parameter' => 'Parameter',
        ],
      ])
      ->setDefaultValue('page')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $pages_description = t("One per line. The '*' character is a wildcard. An example path is /admin/* for every admin pages. Leave in blank to show in all pages. @front_key@ is used to front page", ['@front_key@' => '<front>']);

    $fields['pages'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Pages'))
      ->setDescription($pages_description)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 4,
        ],

      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $parameters_description = t('Parameters for the Modal appear');
    $parameters_description .= '<ul>';
    $parameters_description .= '<li>' . t('Enter one parameters per line') . '</li>';
    $parameters_description .= '<li>' . t('An example path is welcome for show in this parameter') . '</li>';
    $parameters_description .= '<li>' . t('In URL should be /page?modal=welcome') . '</li>';
    $parameters_description .= '</ul>';

    $fields['parameters'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Parameters'))
      ->setDescription($parameters_description)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => [
          'rows' => 4,
        ],
      ])

      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['auto_open'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t("Auto Open"))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['open_modal_on_element_click'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Open this modal clicking on this element'))
      ->setDescription(t('Example: <b>@example_class@</b>. Default is <b>@default_class@</b>', ['@example_class@' => '.open-modal-welcome', '@default_class@' => '.open-modal-page']))
      ->setRequired(FALSE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['roles'] = $modalPageFieldHelper->getFieldRole();

    $fields['enable_dont_show_again_option'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t("Enable option <b>Don't show again</b>"))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -5,
      ]);

    $fields['published'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t("Published"))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -5,
      ]);

    $fields['delay_display'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Delay to display'))
      ->setDescription(t('Value in seconds.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['modal_size'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Modal Size'))
      ->setDescription(t('Select the size of your modal.'))
      ->setRequired(TRUE)
      ->setSettings([
        'allowed_values' => [
          'modal-sm' => 'Small',
          'modal-md' => 'Medium',
          'modal-lg' => 'Large',
        ],
      ])
      ->setDefaultValue('modal-md')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ok_label_button'] = BaseFieldDefinition::create('string')
      ->setLabel(t('OK Label Button'))
      ->setDefaultValue(t('OK'))
      ->setDescription(t('If blank the value will be <b>OK</b>'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Modal entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
