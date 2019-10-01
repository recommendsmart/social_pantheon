<?php

namespace Drupal\nbox_folders\Plugin\Action;

use Drupal\nbox_folders\Entity\NboxFolder;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Move thread to selected folder.
 *
 * @Action(
 *   id = "move_folder_thread",
 *   label = @Translation("Move to folder"),
 *   type = "nbox_metadata",
 *   confirm = FALSE,
 * )
 */
class MoveFolderThread extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $configuration = $this->getConfiguration();
    if (array_key_exists('folder_destination', $configuration)) {
      if ($configuration['folder_destination'] == 0) {
        NboxFolder::removeMetadataFolder($entity);
        return $this->t('Removed from folder');
      }
      else {
        $folder = NboxFolder::load($configuration['folder_destination']);
        $folder->moveMetadataToFolder($entity);
        return $this->t('Moved to folder "@folder"', ['@folder' => $folder->getName()]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($account->hasPermission('use nbox folders') || $account->hasPermission('administer nbox folder')) {
      return TRUE;
    }
    return FALSE;
  }

}
