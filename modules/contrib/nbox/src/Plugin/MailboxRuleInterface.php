<?php

namespace Drupal\nbox\Plugin;

/**
 * Defines an interface MailboxRule objects.
 */
interface MailboxRuleInterface {

  /**
   * Get the fieldname to be used in mailbox rule.
   *
   * @return string
   *   Field name.
   */
  public function getFieldName(): string;

  /**
   * Get the value to be used in mailbox rule.
   *
   * @return string
   *   Value.
   */
  public function getValue(): ?string;

  /**
   * Get the operator to be used in mailbox rule.
   *
   * @return string
   *   Operator.
   */
  public function getOperator(): string;

}
