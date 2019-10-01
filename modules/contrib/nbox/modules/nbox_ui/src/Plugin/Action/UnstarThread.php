<?php

namespace Drupal\nbox_ui\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Set thread to starred.
 *
 * @Action(
 *   id = "unstar_thread",
 *   label = @Translation("Unstar thread"),
 *   type = "nbox_metadata",
 *   confirm = FALSE,
 * )
 */
class UnstarThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $entity */
    $entity->setStarred(FALSE);
    $entity->save();
    return $this->t('Unstarred thread(s).');
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
