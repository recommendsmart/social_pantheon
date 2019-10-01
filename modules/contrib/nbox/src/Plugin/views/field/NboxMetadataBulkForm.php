<?php

namespace Drupal\nbox\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\views_bulk_operations\Plugin\views\field\ViewsBulkOperationsBulkForm;

/**
 * Defines a Nbox metadata operations bulk form element.
 *
 * @ViewsField("nbox_metadata_bulk_form")
 */
class NboxMetadataBulkForm extends ViewsBulkOperationsBulkForm {

  /**
   * {@inheritdoc}
   */
  public function viewsForm(array &$form, FormStateInterface $form_state) {
    parent::viewsForm($form, $form_state);

    $form['header']['bulk_form']['actions']['action_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'action-wrapper',
      ],
    ];

    foreach (Element::children($form['header']['bulk_form']['actions']) as $action) {
      if ($form['header']['bulk_form']['actions'][$action]['#type'] === 'submit') {
        $form['header']['bulk_form']['actions']['action_wrapper'][$action] = $form['header']['bulk_form']['actions'][$action];
        unset($form['header']['bulk_form']['actions'][$action]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function viewsFormSubmit(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#id'] === 'move_to_folder') {
      // We want to pass some context to our action as it is unaware of the form
      // values.
      $this->tempStoreData['configuration'] = ['folder_destination' => $form_state->getValue('folder')];
    }
    return parent::viewsFormSubmit($form, $form_state);
  }

}
