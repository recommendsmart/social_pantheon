<?php

namespace Drupal\dfinance\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validates that a field is unique for the given entity type.
 *
 * @todo Remove once https://www.drupal.org/project/drupal/issues/3080972 is fixed
 */
class AccountCodeIdValidator extends UniqueFieldValueValidator {

  /**
   * {@inheritdoc}
   *
   * Duplicate of parent method with commented out code which causes issue
   * https://www.drupal.org/project/drupal/issues/3080972
   */
  public function validate($items, Constraint $constraint) {
    if (!$item = $items->first()) {
      return;
    }
    $field_name = $items->getFieldDefinition()->getName();
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    //$id_key = $entity->getEntityType()->getKey('id');

    $query = \Drupal::entityQuery($entity_type_id);

    //$entity_id = $entity->id();
    // Using isset() instead of !empty() as 0 and '0' are valid ID values for
    // entity types using string IDs.
    //if (isset($entity_id)) {
    //  $query->condition($id_key, $entity_id, '<>');
    //}

    $value_taken = (bool) $query
      ->condition($field_name, $item->value)
      ->range(0, 1)
      ->count()
      ->execute();

    if ($value_taken) {
      $this->context->addViolation($constraint->message, [
        '%value' => $item->value,
        '@entity_type' => $entity->getEntityType()->getLowercaseLabel(),
        '@field_name' => mb_strtolower($items->getFieldDefinition()->getLabel()),
      ]);
    }
  }

}
