<?php

namespace Drupal\drm_core_farm\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * The confirmation form for deleting an record.
 */
class RecordDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    $entity = $this->getEntity();
    return $this->t('The record %name (%id) has been deleted.', array(
      '%id' => $entity->id(),
      '%name' => $entity->label(),
    ));
  }

}
