<?php

namespace Drupal\prepopulate\Prepopulators;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepopulates entities before their forms are displayed.
 */
class EntityPrepopulator extends Prepopulator {

  /**
   * The entity whose fields are being prepopulated.
   *
   * @var Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Instantiates a new instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   * @param Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @see ContainerInjectionInterface
   */
  public static function create(ContainerInterface $container, EntityInterface $entity) {
    return new static(
      $container->get('request_stack'),
      $entity
    );
  }

  /**
   * Class constructor.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function __construct(RequestStack $request_stack, EntityInterface $entity) {
    parent::__construct($request_stack);
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function hasFormField(string $form_field) {
    if (($this->entity instanceof FieldableEntityInterface) && $this->entity->hasField($form_field)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function setFormField(string $form_field, string $value) {
    $this->entity->set($form_field, $value);
  }

}
