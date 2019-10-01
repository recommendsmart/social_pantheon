<?php

namespace Drupal\nbox\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\user\Entity\User;

/**
 * Defines a class to build a listing of Nbox thread entities.
 *
 * @ingroup nbox
 */
class NboxThreadListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['nbox_thread_id'] = $this->t('ID');
    $header['nbox_subject'] = $this->t('Subject');
    $header['nbox_message_count'] = $this->t('# Messages');
    $header['nbox_senders'] = $this->t('Senders');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\nbox\Entity\NboxThread */
    $senderIds = $entity->getSendersRecipientsInThread(4, TRUE);
    $senderNames = [];
    $i = 0;
    while ($i < 3 && array_key_exists($i, $senderIds)) {
      $senderNames[] = User::load($senderIds[$i])->getDisplayName();
      $i++;
    }
    $senders = implode(', ', $senderNames);
    if (count($senderIds) > 3) {
      $senders .= '...';
    }

    // EntityListBuilder sets the table rows using the #rows property, so we
    // need to add links as render arrays using the 'data' key.
    $row['nbox_thread_id']['data'] = $entity->toLink()->setText($entity->id())->toRenderable();
    $row['nbox_subject']['data'] = $entity->toLink()->setText($entity->getThreadSubject())->toRenderable();
    $row['nbox_message_count'] = count($entity->getMessages());
    $row['nbox_senders'] = $senders;
    return $row + parent::buildRow($entity);
  }

}
