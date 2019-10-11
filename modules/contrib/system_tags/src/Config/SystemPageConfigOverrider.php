<?php

namespace Drupal\system_tags\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\node\NodeInterface;
use Drupal\system_tags\SystemTagFinder\SystemTagFinderManagerInterface;

/**
 * Class SystemPageConfigOverrider.
 *
 * @package Drupal\system_tags\Config
 */
class SystemPageConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * The system tag finder manager.
   *
   * @var \Drupal\system_tags\SystemTagFinder\SystemTagFinderManagerInterface
   */
  protected $tagFinderManager;

  /**
   * SystemPageConfigOverrider constructor.
   *
   * @param \Drupal\system_tags\SystemTagFinder\SystemTagFinderManagerInterface $tagFinderManager
   *   The system tag finder manager.
   */
  public function __construct(SystemTagFinderManagerInterface $tagFinderManager) {
    $this->tagFinderManager = $tagFinderManager;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (in_array('system.site', $names, TRUE)) {
      /** @var \Drupal\system_tags\SystemTagFinder\SystemTagFinderInterface $systemTagFinder */
      $systemTagFinder = $this->tagFinderManager->getInstance(['entity_type' => 'node']);

      if ($node = $systemTagFinder->findOneByTag(SystemTagDefinitions::TAG_ACCESS_DENIED)) {
        $overrides['system.site']['page']['403'] = $this->generatePath($node);
      }

      if ($node = $systemTagFinder->findOneByTag(SystemTagDefinitions::TAG_HOMEPAGE)) {
        $overrides['system.site']['page']['front'] = $this->generatePath($node);
      }

      if ($node = $systemTagFinder->findOneByTag(SystemTagDefinitions::TAG_PAGE_NOT_FOUND)) {
        $overrides['system.site']['page']['404'] = $this->generatePath($node);
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SystemTagsOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * Generate a path to the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string
   *   The path.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function generatePath(NodeInterface $node) {
    return sprintf('/%s', $node->toUrl()->getInternalPath());
  }

}
