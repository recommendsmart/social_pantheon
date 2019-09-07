<?php

namespace Drupal\dfinance\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Provides specific access control for the Finance Supplier entity type.
 *
 * @EntityReferenceSelection(
 *   id = "finance_supplier",
 *   label = @Translation("Finance Supplier Selection"),
 *   entity_types = {"finance_supplier"},
 *   group = "finance_supplier"
 * )
 */
class SupplierSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $configuration = $this->getConfiguration();
    $target_type = $configuration['target_type'];
    $entity_type = $this->entityManager->getDefinition($target_type);

    $query = $this->entityManager->getStorage($target_type)->getQuery();

    // If 'target_bundles' is NULL, all bundles are referenceable, no further
    // conditions are needed.
    if (is_array($configuration['target_bundles'])) {
      // If 'target_bundles' is an empty array, no bundle is referenceable,
      // force the query to never return anything and bail out early.
      if ($configuration['target_bundles'] === []) {
        $query->condition($entity_type->getKey('id'), NULL, '=');
        return $query;
      }
      else {
        $query->condition($entity_type->getKey('bundle'), $configuration['target_bundles'], 'IN');
      }
    }

    if (isset($match)) {
      $searchGroup = $query->orConditionGroup();
      if ($label_key = $entity_type->getKey('label')) {
        $searchGroup->condition($label_key, $match, $match_operator);
      }
      if ($trading_name_key = $entity_type->getKey('trading_name')) {
        $searchGroup->condition($trading_name_key, $match, $match_operator);
      }
      $query->condition($searchGroup);
    }

    // Add entity-access tag.
    $query->addTag($target_type . '_access');

    // Add the Selection handler for system_query_entity_reference_alter().
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    // Add the sort option.
    if ($configuration['sort']['field'] !== '_none') {
      $query->sort($configuration['sort']['field'], $configuration['sort']['direction']);
    }

    if ($status_key = $entity_type->getKey('status')) {
      $query->condition($status_key, TRUE);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function createNewEntity($entity_type_id, $bundle, $label, $uid) {
    /** @var \Drupal\dfinance\Entity\SupplierInterface $supplier */
    $supplier = parent::createNewEntity($entity_type_id, $bundle, $label, $uid);
    $supplier->setPublished();
    return $supplier;
  }

}