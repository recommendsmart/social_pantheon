<?php

namespace Drupal\dfinance\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dfinance\Entity\FinancialDocInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FinanceMenuLinks extends MenuLinkDefault {

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  private $routeMatch;

  /**
   * Used to identify if the Menu Link Route Name has been replaced
   * with the Organisation Select Route Name
   *
   * @see self::getRouteName()
   * @see self::getRouteParameters()
   *
   * @var bool
   */
  private $forceOrgSelectRoute = FALSE;

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('current_route_match')
    );
  }

  /**
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StaticMenuLinkOverridesInterface $static_override, RouteMatchInterface $routeMatch) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $static_override
    );
    $this->routeMatch = $routeMatch;
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
  public function getCacheContexts() {
    return parent::getCacheContexts() + ['url.path'];
  }

  /**
   * @return string|null
   *   Entity ID of the Organisation or NULL if none could be determined
   */
  private function getOrganisationID() {
    if ($organisation = $this->getRouteMatch()->getRawParameter('finance_organisation')) {
      return $organisation;
    }

    $financial_doc = $this->getRouteMatch()->getParameter('financial_doc');
    if ($financial_doc instanceof FinancialDocInterface) {
      return $financial_doc->getOrganisation()->id();
    }

    return NULL;
  }

  /**
   * @inheritdoc
   */
  public function getRouteName() {
    /**
     * Menu Links that use this class are supposed to be contextually aware of which
     * Finance Organisation the user is currently working with based on which Route they
     * are viewing.  The problem comes when these Menu Links are displayed in places
     * where the current Route is not part of an Organisation, such as in the Menu UI,
     * so to prevent an Exception being thrown we change the Route name that this Menu
     * Link uses from whatever it is defined as to the Organisation Select Route.
     */
    if ($this->getOrganisationID() == NULL) {
      $this->forceOrgSelectRoute = TRUE;
      return 'dfinance.organisation_select';
    }

    /**
     * The current Route does relate to a Finance Organisation to return the defined Route
     * Name for this Menu Link
     */
    return parent::getRouteName();
  }

  /**
   * @inheritdoc
   */
  public function getRouteParameters() {
    /**
     * If we are forcing the Organisation Select Route then don't include any
     * Route Parameters, this makes the link look cleaner
     *
     * @see self::getRouteName()
     */
    if ($this->forceOrgSelectRoute) {
      return [];
    }

    return parent::getRouteParameters() + [
      'finance_organisation' => $this->getOrganisationID(),
    ];
  }

}