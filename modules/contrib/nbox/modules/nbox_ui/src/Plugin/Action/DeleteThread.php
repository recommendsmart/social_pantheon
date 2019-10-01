<?php

namespace Drupal\nbox_ui\Plugin\Action;

use Drupal\nbox\Entity\NboxMetadataInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Set the metadata delete status to trash or permanent delete.
 *
 * @Action(
 *   id = "delete_thread",
 *   label = @Translation("Delete"),
 *   type = "nbox_metadata",
 *   confirm = TRUE,
 *   confirm_form_route_name = "nbox_ui.thread.action_delete_confirm"
 * )
 */
class DeleteThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $entity */
    $entity->markDelete();
    $entity->save();
    if ($entity->getDeleteStatus() === NboxMetadataInterface::NBOX_DELETE_MARKED) {
      return $this->t('The message(s) has been moved to trash.');
    }
    if ($entity->getDeleteStatus() === NboxMetadataInterface::NBOX_DELETE_PERMANENT) {
      return $this->t('Permanently deleted message(s).');
    }
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
