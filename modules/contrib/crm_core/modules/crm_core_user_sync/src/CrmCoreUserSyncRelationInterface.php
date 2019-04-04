<?php

namespace Drupal\crm_core_user_sync;

use Drupal\crm_core_contact\IndividualInterface;
use Drupal\user\UserInterface;

/**
 * CrmCoreUserSyncRelation service.
 */
interface CrmCoreUserSyncRelationInterface {

  /**
   * Retrieves the individual contact id for specified user.
   *
   * @return int|null
   *   Individual id, if relation exists.
   */
  public function getUserIndividualId($user_id);

  /**
   * Retrieves the user id for specified individual contact.
   *
   * @return int|null
   *   User id, if relation exists.
   */
  public function getIndividualUserId($individual_id);

  /**
   * Retrieves the relation for specified user.
   *
   * @return int|null
   *   Relation ID, if exists.
   */
  public function getUserRelationId($user_id);

  /**
   * Retrieves the user id for specified individual contact.
   *
   * @return int|null
   *   Relation ID, if exists.
   */
  public function getIndividualRelationId($individual_id);

  /**
   * Synchronizes user and contact.
   *
   * @param \Drupal\user\UserInterface $account
   *   Account to be synchronized. Programmatically created accounts can
   *   override default behavior by setting
   *   $account->crm_core_no_auto_sync = TRUE.
   * @param \Drupal\crm_core_contact\IndividualInterface $contact
   *   Contact to be associated with $account.
   *
   * @return \Drupal\crm_core_contact\ContactInterface
   *   A contact object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function relate(UserInterface $account, IndividualInterface $contact = NULL);

}
