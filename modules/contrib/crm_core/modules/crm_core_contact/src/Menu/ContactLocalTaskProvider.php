<?php

namespace Drupal\crm_core_contact\Menu;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Menu\DefaultEntityLocalTaskProvider;

/**
 * Provides a set of tasks to view, edit and duplicate an entity.
 */
class ContactLocalTaskProvider extends DefaultEntityLocalTaskProvider {

  /**
   * {@inheritdoc}
   */
  public function buildLocalTasks(EntityTypeInterface $entity_type) {
    // See #1834002 and #3044371 for context.
    $link_templates = [];
    foreach (['canonical', 'edit-form', 'duplicate-form', 'delete-form', 'version-history'] as $rel) {
      if ($entity_type->hasLinkTemplate($rel)) {
        $link_templates[] = str_replace('-', '_', $rel);
      }
    }

    $tasks = [];
    if (count($link_templates) > 1) {
      $entity_type_id = $entity_type->id();
      $base = reset($link_templates);

      $titles = [
        'canonical' => $this->t('View'),
        'edit_form' => $this->t('Edit'),
        'duplicate_form' => $this->t('Duplicate'),
        'delete_form' => $this->t('Delete'),
        'version_history' => $this->t('Revisions'),
      ];

      $weight = 0;
      foreach ($link_templates as $rel) {
        $route_name = "entity.$entity_type_id.$rel";
        $tasks[$route_name] = [
          'title' => $titles[$rel],
          'route_name' => $route_name,
          'base_route' => "entity.$entity_type_id.$base",
          'weight' => $weight,
        ];

        $weight += 10;
      }
    }
    return $tasks;
  }

}
