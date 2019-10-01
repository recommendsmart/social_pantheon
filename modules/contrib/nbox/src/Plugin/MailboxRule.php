<?php

namespace Drupal\nbox\Plugin;

/**
 * MailboxRules are value objects to create mailbox queries.
 *
 * Although not an actual plugin, the MailboxRule does live in the Plugin
 * namespace to provide the value objects for the queries against the
 * NboxMetadata base table.
 *
 * @package Drupal\nbox\Plugin
 */
class MailboxRule implements MailboxRuleInterface {

  /**
   * Field name.
   *
   * @var string
   */
  private $fieldName;

  /**
   * Value.
   *
   * @var string|null
   */
  private $value;

  /**
   * Operator.
   *
   * @var string
   */
  private $operator;

  /**
   * MailboxRule constructor.
   *
   * @param string $fieldName
   *   Field name.
   * @param string|null $value
   *   Value.
   * @param string $operator
   *   Operator.
   */
  public function __construct(string $fieldName, ?string $value, string $operator) {
    $this->fieldName = $fieldName;
    $this->value = $value;
    $this->operator = $operator;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName(): string {
    return $this->fieldName;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(): ?string {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperator(): string {
    return $this->operator;
  }

}
