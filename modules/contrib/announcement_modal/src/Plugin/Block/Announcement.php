<?php

namespace Drupal\announcement_modal\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Announcement modal stickey widget' block.
 *
 * @Block(
 *  id = "announcement_stickey_widget",
 *  admin_label = @Translation("Announcement modal stickey widget"),
 * )
 */
class Announcement extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config_factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new Announcement object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('announcement.settings');
    $bannerTitle = $config->get('banner_title');
    $bannerDesc = $config->get('banner_desc.value');
    $imageUrl = '';
    $bannerColor = '';
    if ($config->get('bg_color')) {
      $bannerColor = $config->get('banner_bg');
    }
    if ($fid = $config->get('banner_img')) {
      if ($file = $this->entityTypeManager->getStorage('file')->load(reset($fid))) {
        $imageUrlAbsolute = file_create_url($file->getFileUri());
        $imageUrl = file_url_transform_relative($imageUrlAbsolute);
      }
    }
    // Nothing to display.
    if (!$imageUrl && !$bannerColor) {
      return [];
    }

    return [
      '#markup' => "<div class='stickey-widget'><a role='button' data-toggle='modal' data-target='#announcement'>$bannerTitle</a></div><div id='announcement-banner'></div>",
      '#attached' => [
        'library' => [
          'announcement_modal/announcement_modal.announcements',
        ],
        'drupalSettings' => [
          'announcement' => [
            'image_url' => $imageUrl,
            'image_title' => $bannerTitle,
            'image_desc' => $bannerDesc,
            'image_bg' => $bannerColor,
          ],
        ],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $config = $this->configFactory->get('announcement.settings');
    $access = ($config->get('show_banner')) ? TRUE : FALSE;
    $current_time = strtotime(date('Y-m-d H:i:s', time()));
    $from_date = strtotime($config->get('from_date'));
    $to_date = strtotime($config->get('to_date'));
    if ($access == TRUE) {
      if ($current_time >= $from_date && $current_time <= $to_date) {
        $access = TRUE;
      }
      else {
        $access = FALSE;
      }
    }
    // @TODO check access permission based on given dates.
    // By default, the block is visible.
    return AccessResult::allowedIf($access);
  }

}
