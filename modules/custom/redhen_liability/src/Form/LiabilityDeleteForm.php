<?php

/**
 * @file
 * Contains \Drupal\redhen_liability\Form\LiabilityDeleteForm.
 */

namespace Drupal\redhen_liability\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\redhen_liability\Entity\LiabilityType;

/**
 * Provides a form for deleting Liability entities.
 *
 * @ingroup redhen_liability
 */
class LiabilityDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the @liability-type %name?', [
      '@liability-type' => LiabilityType::load($this->entity->bundle())->label(),
      '%name' => $this->entity->label()
    ]);
  }

}
