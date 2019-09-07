<?php

namespace Drupal\dfinance\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FinancialDocCollection extends ControllerBase implements ContainerInjectionInterface {

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  private $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match')
    );
  }

  public function __construct(RouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
  }

  public function view() {
    /** @var \Drupal\dfinance\Entity\OrganisationInterface $organisation */
    $organisation = $this->routeMatch->getParameter('finance_organisation');

    if ($organisation == null) {
      $displayID = 'all_financial_documents';
      $view = views_embed_view('financial_documents_list', $displayID);
    } else {
      $displayID = 'all_financial_documents_organisation';
      $view = views_embed_view('financial_documents_list', $displayID, $organisation->id());
    }

    if ($view != null) {
      return $view;
    }

    return [
      '#markup' => $this->t('Could not find View with name %view and display_id %display_id', [
        '%view' => 'financial_documents_list',
        '#display_id' => $displayID
      ])
    ];
  }

}