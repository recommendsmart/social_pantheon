<?php

namespace Drupal\nbox_ui\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewExecutable;

/**
 * Set the metadata delete status back to "not deleted".
 *
 * @Action(
 *   id = "restore_thread",
 *   label = @Translation("Restore"),
 *   type = "nbox_metadata",
 *   confirm = FALSE,
 *   requirements = {
 *     "_custom_access" = TRUE,
 *   },
 * )
 */
class RestoreThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\nbox\Entity\NboxMetadata $entity */
    $entity->restoreDelete();
    $entity->save();
    return $this->t('Restored thread(s).');
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

  /**
   * {@inheritdoc}
   */
  public static function customAccess(AccountInterface $account, ViewExecutable $view): bool {
    if ($view->current_display === 'page_1' && $view->args[0] !== 'trash') {
      return FALSE;
    }
    return parent::customAccess($account, $view);
  }

}
