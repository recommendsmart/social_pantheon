<?php

namespace Drupal\crm_core_contact;

/**
 * Defines methods for individual entities.
 */
interface IndividualInterface extends ContactInterface {

  /**
   * Sets main email of individual.
   *
   * @param array $email
   *   Main email.
   */
  public function setMainEmail(array $email);

  /**
   * Gets main email of individual.
   *
   * @return array
   *   Main email of individual.
   */
  public function getMainEmail();

  /**
   * Sets email to email list.
   *
   * @param array $email
   *   Email to add to list.
   */
  public function addEmail(array $email);

  /**
   * Gets email list for individual.
   *
   * @return array
   *   Email list.
   */
  public function getEmailList();

  /**
   * Sets birth date of individual.
   *
   * @param int $birth_date
   *   Birth date field.
   */
  public function setBirthDate($birth_date);

  /**
   * Gets birth date of individual.
   *
   * @return int
   *   Birth date of individual.
   */
  public function getBirthDate();

  /**
   * Sets sex field of individual.
   *
   * @param string $sex
   *   Sex field.
   */
  public function setSex($sex);

  /**
   * Gets sex of individual.
   *
   * @return string
   *   Sex of individual.
   */
  public function getSex();

}
