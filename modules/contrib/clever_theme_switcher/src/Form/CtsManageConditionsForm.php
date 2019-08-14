<?php

namespace Drupal\clever_theme_switcher\Form;

use Drupal\clever_theme_switcher\Helper\ConditionsFormHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;

/**
 * Form handler for the Cts add and edit forms.
 */
class CtsManageConditionsForm extends EntityForm {

  use ConditionsFormHelper;

  /**
   * Constructs an CtsAddForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->getEntity();

    $form['add_condition'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new condition'),
      '#url' => Url::fromRoute('conditions.list', [
        'entity' => $entity->getId(),
      ]),
      '#attributes' => NestedArray::mergeDeep($this->getAttributes(), [
        'class' => [
          'use-ajax',
          'button-action',
        ],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => '768',
        ]),
      ]),
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
      '#weight' => 1,
    ];
    return $this->helper($form, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    return [];
  }

}
