<?php

namespace Drupal\nbox\Entity\Field;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class ThreadMessages handles the messages per thread computed field.
 *
 * @package Drupal\nbox\Entity\Field
 */
class ThreadMessages extends EntityReferenceFieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $delta = 0;
    $entity = $this->getEntity();
    $storage = \Drupal::entityTypeManager()->getStorage('nbox');
    $messages = $storage->loadByThread($entity);
    foreach ($messages as $nbox) {
      $this->list[$delta] = $this->createItem($delta, $nbox->id());
      $delta++;
    }
  }

}
