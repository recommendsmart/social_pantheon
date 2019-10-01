<?php

namespace Drupal\nbox\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\nbox\Plugin\MailboxManager;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide a filter for the applicable mailbox.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("nbox_view_mailbox")
 */
class NboxViewMailbox extends FilterPluginBase {

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
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    $options = [];
    foreach ($this->mailboxManager->getDefinitions() as $mailboxDefinition) {
      $options[$mailboxDefinition['id']] = $mailboxDefinition['label']->render();
    }

    $form['value'] = [
      '#empty_option' => $this->t('Select a mailbox'),
      '#type' => 'select',
      '#title' => $this->t('Mailbox'),
      '#options' => $options,
      '#default_value' => $this->value,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $viewsFilterQuery = $this->mailboxManager->createInstance($this->value)->getViewsFilterQueryRules();

    /** @var \Drupal\nbox\Plugin\MailboxRule $filter */
    foreach ($viewsFilterQuery['rules'] as $filter) {
      $field_name = $filter->getFieldName();
      $field = "$this->tableAlias.$field_name";
      $this->query->addWhere($this->options['group'], $field, $filter->getValue(), $filter->getOperator());
    }
  }

}
