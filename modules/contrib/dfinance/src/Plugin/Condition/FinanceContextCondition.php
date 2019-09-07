<?php

namespace Drupal\dfinance\Plugin\Condition;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dfinance\Entity\FinancialDocInterface;

/**
 * Determines whether to show the Drupal Finance Menu
 *
 * @Condition(
 *   id = "finance_context",
 *   label = @Translation("Drupal Finance"),
 * )
 */
class FinanceContextCondition extends ConditionPluginBase implements ConditionInterface {

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  private $routeMatch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
    $this->routeMatch = \Drupal::routeMatch();
  }

  /**
   * Get the current Route Match
   *
   * @return \Drupal\Core\Routing\RouteMatchInterface
   */
  private function getRouteMatch() {
    return $this->routeMatch;
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['show_if_organisation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show only if the current page is within a Financial Organisation'),
      '#description' => $this->t('This condition is most useful for the Drupal Finance Menu where links are contextually aware of the current Financial Organisation.  The logic that is evaluated here checks the current route and in the case of Finance Documents checks if the document is linked to an Organisation.'),
      '#default_value' => $this->configuration['show_if_organisation'],
      '#weight' => -1,
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['show_if_organisation'] = $form_state->getValue('show_if_organisation');
  }

  /**
   * @inheritdoc
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'show_if_organisation' => false,
    ];
  }

  /**
   * @inheritdoc
   */
  public function summary() {
    return $this->t('%restricted%negated', [
      '%restricted' => $this->evaluate() ? 'Restricted' : 'Not restricted',
      '%negated' => $this->isNegated() ? ', negated' : '',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function evaluate() {
    if (!$this->configuration['show_if_organisation']) {
      // Always show the menu if the condition hasn't been activated
      return true;
    }
    if ($this->getRouteMatch()->getRawParameter('finance_organisation') != null) {
      return true;
    }
    $financial_doc = $this->getRouteMatch()->getParameter('financial_doc');
    if ($financial_doc instanceof FinancialDocInterface) {
      return $financial_doc->getOrganisation() != null;
    }
    return false;
  }

}