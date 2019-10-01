<?php

namespace Drupal\nbox\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\SortArray;

/**
 * Manages Mailbox plugin implementations.
 *
 * @see \Drupal\nbox\Annotation\Mailbox
 * @see \Drupal\nbox\Plugin\MailboxInterface
 * @see \Drupal\nbox\Plugin\MailboxBase
 * @see plugin_api
 */
class MailboxManager extends DefaultPluginManager {

  /**
   * Constructs a new MailboxManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Mailbox', $namespaces, $module_handler, 'Drupal\nbox\Plugin\MailboxInterface', 'Drupal\nbox\Annotation\Mailbox');
    $this->alterInfo('nbox_mailbox_info');
    $this->setCacheBackend($cache_backend, 'nbox_mailboxes');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();
    uasort($definitions, [SortArray::class, 'sortByWeightElement']);
    return $definitions;
  }

}
