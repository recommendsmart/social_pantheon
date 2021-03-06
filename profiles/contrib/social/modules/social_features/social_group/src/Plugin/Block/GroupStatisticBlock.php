<?php

namespace Drupal\social_group\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'GroupStatisticBlock' block.
 *
 * @Block(
 *  id = "group_statistic_block",
 *  admin_label = @Translation("Group statistic block"),
 *  context = {
 *    "group" = @ContextDefinition("entity:group", required = FALSE)
 *  }
 * )
 */
class GroupStatisticBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a GroupHeroBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $group = _social_group_get_current_group();

    if (!empty($group)) {
      // Content.
      $content = $this->entityTypeManager
        ->getViewBuilder('group')
        ->view($group, 'statistic');

      $build['content'] = $content;
      // Cache tags.
      $build['#cache']['tags'][] = 'group_block:' . $group->id();
    }
    // Cache contexts.
    $build['#cache']['contexts'][] = 'url.path';
    $build['#cache']['max-age'] = 0;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show statistic block only when new style is enabled.
    if (theme_get_setting('style') === 'sky') {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
