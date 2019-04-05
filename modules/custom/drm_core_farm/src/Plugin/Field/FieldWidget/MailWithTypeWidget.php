<?php

namespace Drupal\drm_core_record\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EmailDefaultWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Email;

/**
 * Plugin implementation of the 'mail_type' widget.
 *
 * @FieldWidget(
 *   id = "email_with_type",
 *   label = @Translation("Email with type"),
 *   field_types = {
 *     "email_with_type"
 *   },
 *   multiple_values = TRUE
 * )
 */
class MailWithTypeWidget extends EmailDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'details';
    $element['#open'] = TRUE;
    $element['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#default_value' => isset($items[$delta]->type) ? $items[$delta]->type : NULL,
      '#options' => $this->fieldDefinition->getFieldStorageDefinition()->getSetting('email_types'),
    ];
    $element['value'] = [
      '#title' => $this->t('Email'),
      '#type' => 'email',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#placeholder' => $this->getSetting('placeholder'),
      '#size' => $this->getSetting('size'),
      '#maxlength' => Email::EMAIL_MAX_LENGTH,
    ];
    return $element;
  }

}
