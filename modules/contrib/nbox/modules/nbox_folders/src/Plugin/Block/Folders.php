<?php

namespace Drupal\nbox_folders\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\nbox_folders\Entity\Storage\NboxFolderStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a block with the folders.
 *
 * @Block(
 *   id = "folders",
 *   admin_label = @Translation("Folders"),
 * )
 */
class Folders extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Folder storage.
   *
   * @var \Drupal\nbox_folders\Entity\Storage\NboxFolderStorage
   */
  protected $folderStorage;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Folders constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\nbox_folders\Entity\Storage\NboxFolderStorage $folderStorage
   *   Folder storage.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, NboxFolderStorage $folderStorage, AccountProxyInterface $currentUser, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->folderStorage = $folderStorage;
    $this->currentUser = $currentUser;
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
      $container->get('entity_type.manager')->getStorage('nbox_folder'),
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $addUrl = Url::fromRoute('entity.nbox_folder.add_form');
    $list = [];
    $folders = $this->folderStorage->loadByUser($this->currentUser->id());
    $currentUri = $this->request->getRequestUri();
    /** @var \Drupal\nbox_folders\Entity\NboxFolder $folder */
    foreach ($folders as $key => $folder) {
      $url = Url::fromRoute('view.nbox_folder.page_1', [
        'arg_0' => $key,
      ]);
      $activeClass = $currentUri === $url->toString() ? 'active' : 'not-active';
      $mailbox = $folder->getName();
      $list[$key] = [
        '#type' => 'link',
        '#title' => $mailbox,
        '#url' => $url,
        '#wrapper_attributes' => ['class' => ['folder', $activeClass]],
        '#attributes' => ['class' => ['']],
      ];
    }
    $block = [
      '#type' => 'container',
      '#title' => $this->t('Folders'),
    ];
    $block['folders'] = [
      '#theme' => 'item_list',
      '#items' => $list,
      '#attached' => [
        'library' => [
          'nbox_ui/mailboxes',
        ],
      ],
    ];
    $block['add_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Add folder'),
      '#url' => $addUrl,
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
    $block['manage_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Manage folders'),
      '#url' => Url::fromRoute('view.nbox_folder_management.page_1'),
      '#options' => [
        'attributes' => [
          'class' => [
            'button',
            'button--primary',
          ],
        ],
      ],
    ];
    $block['#attributes']['class'][] = 'nbox_list_boxes';

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'block',
      'block:folders',
      'block:folders:' . $this->currentUser->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'user',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'use nbox folders');
  }

}
