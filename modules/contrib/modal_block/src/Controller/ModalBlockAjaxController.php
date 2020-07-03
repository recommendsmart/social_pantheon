<?php

namespace Drupal\modal_block\Controller;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\modal_block\Plugin\Block\ModalBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller responses to load modal block by ajax.
 *
 * The response is a set of AJAX commands to update the
 * link in the page.
 */
class ModalBlockAjaxController extends ControllerBase {

  /**
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * ModalBlockAjaxController constructor.
   *
   * @param \Drupal\Core\Block\BlockManager $block_manager
   */
  public function __construct(BlockManager $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Return block render array for modal block instance.
   * @param \Drupal\block\Entity\Block $block
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function handle(Block $block) {
    $content = [];
    $config = $block->getPlugin()->getConfiguration();
    $block_id = $config['block']['id'] ?? FALSE;
    if ($block_id && ($block_instance = $this->blockManager->createInstance($block_id))) {
      $content = $block_instance->build();
    }
    return $content;
  }

  /**
   * @param \Drupal\block\Entity\Block $block
   * @param \Drupal\Core\Session\AccountInterface $account
   *  Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public static function access(Block $block, AccountInterface $account) {
    $block_plugin = $block->getPlugin();
    if ($block_plugin instanceof ModalBlock) {
      if ($block_id = $block_plugin->getConfiguration()['block']['id'] ?? FALSE) {
        if ($content_block = \Drupal::service('plugin.manager.block')->createInstance($block_id)) {
          return $content_block->access($account, TRUE);
        }
      }
    }
    return AccessResult::forbidden();
  }

}
