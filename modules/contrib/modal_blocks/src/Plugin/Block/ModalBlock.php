<?php

namespace Drupal\modal_blocks\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a 'ModalBlock' block.
 *
 * @Block(
 *  id = "modal_block",
 *  admin_label = @Translation("Modal block"),
 * )
 */
class ModalBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $block = $config['block'];
    $block_object = Block::load($block);
    $form['block'] = array(
      '#type' => 'entity_autocomplete',
      '#title' => 'Block',
      '#target_type' => 'block',
      '#default_value' => isset($block_object) ? $block_object : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('block', $form_state->getValue('block'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $block = $config['block'];
    $block = Block::load($block);
    $block_content = \Drupal::entityManager()
      ->getViewBuilder('block')
      ->view($block);
    $block_render = array('#markup' => drupal_render($block_content));
    $modal_block[] = array(
      '#theme' => 'modal_block_formatter',
      '#block' => $block_render,
    );
    return $modal_block;
  }

}
