<?php

namespace Drupal\nbox\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestination;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide a field for starring / unstarring the nbox metadata.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("nbox_view_star_action")
 */
class NboxViewStar extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestination
   */
  protected $destination;

  /**
   * NboxViewStar constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Routing\RedirectDestination $destination
   *   Redirect destination.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RedirectDestination $destination) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->destination = $destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $link = 'Unstarred';

    // First check the referenced entity.
    $nboxMetadata = $values->_entity;

    $type = get_class($nboxMetadata);
    if ($type === 'Drupal\nbox\Entity\NboxMetadata') {
      $star = $nboxMetadata->getStarred() ? 'Starred' : 'Unstarred';
      $link = Link::createFromRoute(
        $star,
        'entity.nbox_metadata.star',
        [
          'nbox_metadata' => $nboxMetadata->id(),
        ],
        [
          'query' => $this->destination->getAsArray(),
        ]
      )->toString();
    }
    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
