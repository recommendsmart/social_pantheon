<?php

namespace Drupal\dfinance\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrganisationSelect extends ControllerBase {

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
  private $container;

  public static function create(ContainerInterface $container) {
    return new static($container);
  }

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Gets the currently active Container
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private function getContainer() {
    return $this->container;
  }

  public function page() {
    $entity_type = 'finance_organisation';
    $entity_storage = $this->entityTypeManager()->getStorage($entity_type);

    /** @var array $entity_ids */
    $entity_ids = $entity_storage->getQuery()->execute();

    if (count($entity_ids) == 1) {
      // We use reset() to get the Entity ID because the array key is also the Entity ID, so
      // not a predictable key
      $entity_id = reset($entity_ids);

      $entity = $entity_storage->load($entity_id);
      return new RedirectResponse($entity->toUrl()->toString());
    }

    return OrganisationListBuilder::createInstance($this->getContainer(), $entity_storage->getEntityType())->render();
  }

}