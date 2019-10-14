<?php

namespace Drupal\get_a_quote\Controller;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;


/**
 * Provides the checkout form page.
 */
class QuoteController extends ControllerBase {

  /**
   * Builds and processes the form provided by the order's checkout flow.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The render form.
   */
/*   public function quotePage(RouteMatchInterface $route_match) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order 
    $order = $route_match->getParameter('commerce_order');
    $requested_step_id = $route_match->getParameter('step');
	
    $step_id = $this->checkoutOrderManager->getCheckoutStepId($order, $requested_step_id);
    if ($requested_step_id != $step_id) {
      $url = Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id(), 'step' => $step_id]);
      return new RedirectResponse($url->toString());
    }
    $checkout_flow = $this->checkoutOrderManager->getCheckoutFlow($order);
    $checkout_flow_plugin = $checkout_flow->getPlugin();

    return $this->formBuilder->getForm($checkout_flow_plugin, $step_id);
  } */
  
  public function quotelink(RouteMatchInterface $route_match) {
	
	$order = $route_match->getParameter('commerce_order');
    $requested_step_id = $route_match->getParameter('step');
    echo'<pre>'; print_r($order); exit;
    $build = array(
      '#type' => 'markup',
      '#markup' => t('Get a Quote'),
    );
    return $build;
  }

}
