<?php

namespace Drupal\modal_page;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * The trait setters of Modal Page module.
 */
trait ModalPageSettersTrait {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Module handler.
   *
   * @var Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Set Module Handler.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function setModuleHandler(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
    return $this;
  }

  /**
   * Set Language Manager.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function setLanguageManager(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
    return $this;
  }

}
