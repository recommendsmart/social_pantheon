<?php

namespace Drupal\dfinance;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dfinance\Entity\FinancialDocInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;

class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $financial_doc = $route_match->getParameter('financial_doc');
    return $financial_doc instanceof FinancialDocInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var \Drupal\dfinance\Entity\FinancialDocInterface $financial_doc */
    $financial_doc = $route_match->getParameter('financial_doc');
    $collection_route = $financial_doc->toUrl('collection')->getRouteName();

    $breadcrumb = parent::build($route_match);
    $new_breadcrumb = new Breadcrumb();

    // Swap the Financial Doc Collection for the Organisation if we have one otherwise do nothing
    $links = $breadcrumb->getLinks();
    foreach ($links as $link) {
      $organisation = $financial_doc->getOrganisation();
      if ($link->getUrl()->getRouteName() == $collection_route && $organisation != NULL) {
        $new_breadcrumb->addLink($organisation->toLink());
      } else {
        $new_breadcrumb->addLink($link);
      }
    }

    // We need to make sure that the breadcrumb is re-cached any time the Financial Doc is updated
    // in case the Organisation is changed or removed
    $new_breadcrumb->addCacheTags($financial_doc->getCacheTags());

    // Copy the Cache Tags and Contexts from the original Breadcrumb
    $new_breadcrumb->addCacheContexts($breadcrumb->getCacheContexts());
    $new_breadcrumb->addCacheTags($breadcrumb->getCacheTags());

    return $new_breadcrumb;
  }

}
