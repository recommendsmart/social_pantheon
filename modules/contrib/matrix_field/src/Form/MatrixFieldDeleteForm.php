<?php

namespace Drupal\matrix_field\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Matrix field entities.
 */
class MatrixFieldDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('matrix_field.matrix_fields_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    drupal_set_message(
      $this->t('content @type: deleted @label.',
        [
          '@type' => $this->entity->bundle(),
          '@label' => $this->entity->label(),
        ]
        )
    );
    // If matrix field is indexed by search API,
    // we should remove it from index and then
    // remove facets with this field and remove facets from page manager pages
    // if they are.
    if (\Drupal::moduleHandler()->moduleExists('search_api')) {
      $entityTypeManager = \Drupal::entityTypeManager();
      $indexStorage = $entityTypeManager->getStorage('search_api_index');
      $indexes = $indexStorage->loadMultiple();
      /** @var \Drupal\search_api\Entity\Index $index */
      foreach ($indexes as $index) {
        $fields = $index->getFields();
        $definitions = $index->getPropertyDefinitions(NULL);
        foreach ($fields as $id => $field) {
          if (!isset($definitions[$id])) {
            $path = $field->getPropertyPath();
            if (!$field->getDatasourceId()) {
              $index->removeField($path);
              $index->save();
              if (\Drupal::moduleHandler()->moduleExists('facets')) {
                $facetStorage = $entityTypeManager->getStorage('facets_facet');
                // Check if facet exists
                $facets = $facetStorage->loadByProperties(['field_identifier' => $id]);
                if (!empty($facets)) {
                  $facet = reset($facets);
                  if (\Drupal::moduleHandler()->moduleExists('page_manager')) {
                    $pageVariantStorage = $entityTypeManager->getStorage('page_variant');
                    $block_id = 'facet_block:' . $facet->id();
                    $page_variants = $pageVariantStorage->loadMultiple();
                    /** @var \Drupal\page_manager\Entity\PageVariant $page_variant */
                    foreach ($page_variants as $page_variant) {
                      $settings = $page_variant->get('variant_settings');
                      if (!isset($settings['blocks'])) {
                        continue;
                      }
                      foreach ($settings['blocks'] as $uuid => $block) {
                        if ($block['id'] !== $block_id) {
                          continue;
                        }
                        unset($settings['blocks'][$uuid]);
                        $page_variant->set('variant_settings', $settings);
                        $page_variant->save();
                      }
                    }
                  }
                  $facet->delete();
                }
              }
            }
          }
        }
      }
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
