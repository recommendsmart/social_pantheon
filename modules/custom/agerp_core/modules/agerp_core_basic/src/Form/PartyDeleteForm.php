<?php

namespace Drupal\agerp_core_basic\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * The confirmation form for deleting an party.
 */
class PartyDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    $entity = $this->getEntity();
    return $this->t('The party %name (%id) has been deleted.', [
      '%id' => $entity->id(),
      '%name' => $entity->label(),
    ]);
  }

}
