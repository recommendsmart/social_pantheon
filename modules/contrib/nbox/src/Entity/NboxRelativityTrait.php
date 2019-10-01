<?php

namespace Drupal\nbox\Entity;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\user\Entity\User;

/**
 * Nbox UX includes various strings that vary depending on the context.
 *
 * @package Drupal\nbox\Entity
 */
trait NboxRelativityTrait {

  /**
   * Get the relative username.
   *
   * @param int $uid
   *   User ID.
   *
   * @return string
   *   User name.
   */
  public function relativeUserName($uid): string {
    $userNames = $this->relativeUserNameMultiple([$uid]);
    return reset($userNames);
  }

  /**
   * Get the relative user names.
   *
   * @param array $uids
   *   User IDs.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   User names.
   */
  public function relativeUserNameMultiple(array $uids): array {
    $userNames = [];
    foreach ($uids as $uid) {
      $userNames[$uid] = (int) $uid === (int) \Drupal::currentUser()->id() ? t('Me') : User::load($uid)->getDisplayName();
    }
    return $userNames;
  }

  /**
   * Transform a timestamp to a relative formatted date.
   *
   * @param int $timestamp
   *   Timestamp.
   *
   * @return string
   *   Formatted date.
   */
  public function dateToRelative(int $timestamp): string {
    return date($this->getRelativeFormat($timestamp), $timestamp);
  }

  /**
   * Get the relative date format for a timestamp.
   *
   * @param int $timestamp
   *   Timestamp.
   *
   * @return string
   *   Date format.
   */
  public function getRelativeFormat(int $timestamp): string {
    $configStore = \Drupal::config('nbox.settings');
    if (date('d-m-Y', $timestamp) === date('d-m-Y', REQUEST_TIME)) {
      $format = $configStore->get('date_today');
    }
    else {
      $format = $configStore->get('date_other');
    }
    return DateFormat::load($format)->getPattern();
  }

}
