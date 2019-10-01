<?php

namespace Drupal\nbox\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for Mailbox plugins.
 *
 * @see \Drupal\nbox\Annotation\Mailbox
 * @see \Drupal\nbox\
 * @see \Drupal\nbox\Plugin\MailboxInterface
 * @see plugin_api
 */
abstract class MailboxBase extends PluginBase implements MailboxInterface, ContainerFactoryPluginInterface {

  /**
   * Rules set.
   *
   * @var array
   */
  protected $rules;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * MailboxBase constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   Module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection, AccountProxyInterface $currentUser, ModuleHandler $moduleHandler, CacheBackendInterface $cache) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->rules = [];
    $this->setViewsFilterQuery();
    $this->connection = $connection;
    $this->currentUser = $currentUser;
    $this->moduleHandler = $moduleHandler;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('current_user'),
      $container->get('module_handler'),
      $container->get('cache.default')
    );
  }

  /**
   * {@inheritdoc}
   */
  abstract public function setViewsFilterQuery();

  /**
   * {@inheritdoc}
   */
  public function getViewsFilterQueryRules() :array {
    $this->rules[] = new MailboxRule('uid', $this->currentUser->id(), '=');

    $viewsQueryData = [
      'id' => $this->getPluginId(),
      'rules' => $this->rules,
    ];
    $this->moduleHandler->alter('nbox_views_filter_query', $viewsQueryData);
    return $viewsQueryData;
  }

  /**
   * Get the number of unread messages per user, per mailbox.
   */
  public function getUnreadPerMailbox() :int {
    $currentUserId = $this->currentUser->id();
    $cacheId = 'unread_list:' . $currentUserId . ':' . $this->getPluginId();
    if ($item = $this->cache->get($cacheId)) {
      return $item->data;
    }
    $this->rules[] = new MailboxRule('uid', $currentUserId, '=');
    $this->rules[] = new MailboxRule('read', 0, '=');
    $connection = $this->connection;
    $query = $connection->select('nbox_metadata', 'nm');
    $query->fields('nm', ['nbox_thread_id']);
    foreach ($this->rules as $rule) {
      $query->condition('nm.' . $rule->getFieldName(), $rule->getValue(), $rule->getOperator());
    }
    $results = $query->countQuery()->execute()->fetchField();
    $this->cache->set($cacheId, $results, Cache::PERMANENT, ['unread_list:' . $currentUserId]);
    return $results;
  }

}
