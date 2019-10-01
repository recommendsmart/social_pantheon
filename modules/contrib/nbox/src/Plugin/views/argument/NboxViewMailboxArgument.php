<?php

namespace Drupal\nbox\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\nbox\Plugin\MailboxManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide a contextual filter to select the mailbox.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsArgument("nbox_view_mailbox_argument")
 */
class NboxViewMailboxArgument extends ArgumentPluginBase {

  /**
   * Mailbox manager.
   *
   * @var \Drupal\nbox\Plugin\MailboxManager
   */
  protected $mailboxManager;

  /**
   * NboxViewMailbox constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\nbox\Plugin\MailboxManager $mailboxManager
   *   Mailbox manager.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, MailboxManager $mailboxManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailboxManager = $mailboxManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mailbox')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateArgument($arg) {
    $existingMailboxes = array_keys($this->mailboxManager->getDefinitions());
    if (parent::validateArgument($arg) && in_array($arg, $existingMailboxes)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = TRUE) {
    $this->ensureMyTable();
    $viewsFilterQuery = $this->mailboxManager->createInstance($this->argument)->getViewsFilterQueryRules();

    /** @var \Drupal\nbox\Plugin\MailboxRule $filter */
    foreach ($viewsFilterQuery['rules'] as $filter) {
      $field_name = $filter->getFieldName();
      $field = "$this->tableAlias.$field_name";
      $this->query->addWhere(0, $field, $filter->getValue(), $filter->getOperator());
    }
  }

}
