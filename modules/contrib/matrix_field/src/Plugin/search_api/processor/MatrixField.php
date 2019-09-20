<?php

namespace Drupal\matrix_field\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api\Item\ItemInterface;

/**
 * Adds the item's matrix fields to the indexed data.
 *
 * @SearchApiProcessor(
 *   id = "matrix_field",
 *   label = @Translation("Matrix Field"),
 *   description = @Translation("Adds the item's matrix fields to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 * )
 */
class MatrixField extends ProcessorPluginBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $matrixFieldStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->matrixFieldStorage = $container->get('entity_type.manager')->getStorage('matrix_field');

    return $processor;
  }
  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $matrixFields = $this->matrixFieldStorage->loadMultiple();
      foreach ($matrixFields as $matrixField) {
        $type = 'string';
        if ($matrixField->get('field_type') === 'decimal') {
          $type = 'number';
        }
        if ($matrixField->get('field_type') === 'boolean') {
          $type = 'boolean';
        }
        $definition = [
          'label' => $matrixField->label(),
          'type' => $type,
          'processor_id' => $this->getPluginId(),
          'originalType' => 'matrix_field',
        ];
        $properties[$matrixField->id()] = new ProcessorProperty($definition);
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject();
    $fields = $item->getFields();
    foreach ($fields as $field_id => $field) {
      if (method_exists($field->getDataDefinition(), 'getProcessorId') &&
        $field->getDataDefinition()->getProcessorId() === 'matrix_field') {
        $properties = $entity->getProperties();
        foreach ($properties as $key => $property) {
          $definition = $property->getDataDefinition();
          if ($definition->getType() === 'matrix_field') {
            $values = $entity->get($key);
            if ($values->count()) {
              foreach ($values as $value) {
                if ($value->field_id === $field->getPropertyPath()) {
                  $field->addValue($value->field_value);
                }
              }
            }
          }
        }
      }
    }
  }
}