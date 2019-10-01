<?php

namespace Drupal\nbox_ui\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Set thread to starred.
 *
 * @Action(
 *   id = "star_thread",
 *   label = @Translation("Star thread"),
 *   type = "nbox_metadata",
 *   confirm = FALSE,
 * )
 */
class StarThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $entity */
    $entity->setStarred(TRUE);
    $entity->save();
    return $this->t('Starred thread(s).');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->access('star', $account, TRUE);
    if ($object->getEntityType() === 'nbox_metadata') {
      $access->andIf($object->status->access('star', $account, TRUE));
    }
    return $return_as_object ? $access : $access->isAllowed();
  }

}
