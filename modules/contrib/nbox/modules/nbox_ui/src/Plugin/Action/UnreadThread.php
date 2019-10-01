<?php

namespace Drupal\nbox_ui\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Set the metadata to "read".
 *
 * @Action(
 *   id = "unread_thread",
 *   label = @Translation("Mark unread"),
 *   type = "nbox_metadata",
 *   confirm = FALSE,
 * )
 */
class UnreadThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $entity */
    $entity->setRead(FALSE);
    $entity->save();
    return $this->t('The message(s) have been marked as unread.');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->access('update', $account, TRUE);
    if ($object->getEntityType() === 'nbox_metadata') {
      $access->andIf($object->status->access('update', $account, TRUE));
    }
    return $return_as_object ? $access : $access->isAllowed();
  }

}
