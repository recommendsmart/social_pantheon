<?php

namespace Drupal\nbox_ui\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Provides a block with the compose message link.
 *
 * @Block(
 *   id = "compose",
 *   admin_label = @Translation("Compose"),
 * )
 */
class ComposeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = Url::fromRoute('entity.nbox.add_form', [
      'nbox_type' => 'message',
    ]);
    return [
      '#type' => 'link',
      '#title' => $this->t('Compose'),
      '#url' => $url,
      '#options' => [
        'attributes' => [
          'class' => [
            'button',
            'button-action',
            'button--primary',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'use nbox');
  }

}
