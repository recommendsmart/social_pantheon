<?php

namespace Drupal\nbox\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for UniqueThreadUser.
 *
 * Class UniqueThreadUserValidator.
 *
 * @package Drupal\nbox\Plugin\Validation\Constraint
 */
class UniqueThreadUserValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Constructs a new UniqueThreadUserValidator.
   *
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   *   Entity manager.
   */
  public function __construct(EntityManager $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    $entityTypeId = $entity->getEntityTypeId();
    $combinationExists = (bool) $this->entityManager->getStorage($entityTypeId)->getQuery()
      ->condition('nbox_thread_id', $entity->nbox_thread_id->target_id)
      ->condition('uid', $entity->uid->target_id)
      ->range(0, 1)
      ->count()
      ->execute();
    if ($combinationExists) {
      $this->context->addViolation($constraint->notUniquePair);
    }
  }

}
