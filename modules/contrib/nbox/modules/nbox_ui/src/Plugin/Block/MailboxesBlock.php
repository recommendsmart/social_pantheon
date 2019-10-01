<?php

namespace Drupal\nbox_ui\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\nbox\Plugin\MailboxManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a block with all the mailboxes.
 *
 * @Block(
 *   id = "mailboxes",
 *   admin_label = @Translation("Mailboxes"),
 * )
 */
class MailboxesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Mailbox manager.
   *
   * @var \Drupal\nbox\Plugin\MailboxManager
   */
  protected $mailboxManager;

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * MailboxesBlock constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\nbox\Plugin\MailboxManager $mailboxManager
   *   Mailbox manager.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailboxManager $mailboxManager, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailboxManager = $mailboxManager;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mailbox'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $list = [];
    $currentUri = $this->request->getRequestUri();
    foreach ($this->mailboxManager->getDefinitions() as $key => $mailboxDefinition) {
      $url = Url::fromRoute('view.nbox_mailbox.page_1', [
        'arg_0' => $mailboxDefinition['id'],
      ]);
      $activeClass = $currentUri === $url->toString() ? 'active' : 'not-active';
      $mailbox = $mailboxDefinition['label']->render();
      $list[$key] = [
        '#type' => 'link',
        '#title' => $mailbox,
        '#url' => $url,
        '#wrapper_attributes' => ['class' => ['mailbox-' . strtolower($mailbox), $activeClass]],
        '#attributes' => ['class' => ['']],
      ];

      if ($mailboxDefinition['showUnread'] && $this->mailboxManager->createInstance($key)->getUnreadPerMailbox() > 0) {
        $list[$key]['#suffix'] = '<span class="unread">' . $this->mailboxManager->createInstance($key)->getUnreadPerMailbox() . '</span>';
      }
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $list,
      '#attached' => [
        'library' => [
          'nbox_ui/mailboxes',
        ],
      ],
    ];
    $build['#attributes']['class'][] = 'nbox_list_boxes';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'use nbox');
  }

}
