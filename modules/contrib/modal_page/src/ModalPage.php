<?php

namespace Drupal\modal_page;

use Drupal\Component\Utility\Xss;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Modal Page Class.
 */
class ModalPage {

  use StringTranslationTrait;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Path Matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The user current.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Construct of Modal Page service.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   Path Matcher.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The user current.
   */
  public function __construct(LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_manager, ConfigFactoryInterface $config_factory, Connection $database, RequestStack $request_stack, PathMatcherInterface $path_matcher, UuidInterface $uuid_service, AccountProxyInterface $current_user) {
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_manager;
    $this->pathMatcher = $path_matcher;
    $this->request = $request_stack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->uuidService = $uuid_service;
    $this->currentUser = $current_user;
  }

  /**
   * Function to check Modal will show.
   */
  public function checkModalToShow() {

    $modalToShow = $this->getModalToShow();
    if (empty($modalToShow)) {
      return FALSE;
    }

    $button = $this->t('OK');
    if (!empty($modalToShow->ok_label_button->value)) {
      $button = $this->clearText($modalToShow->ok_label_button->value);
    }

    return [
      'id' => $modalToShow->id->value,
      'title' => $this->clearText($modalToShow->title->value),
      'text' => $this->getAutheticatedUserName($this->clearText($modalToShow->body->value)),
      'delay_display' => $modalToShow->delay_display->value,
      'modal_size' => $modalToShow->modal_size->value,
      'button' => $button,
      'do_not_show_again' => $this->t('Do not show again'),
    ];
  }

  /**
   * Get modal to show.
   *
   * @return object
   *   Return the modal to show.
   */
  public function getModalToShow() {
    $modalToShow = FALSE;
    $currentPath = $this->getCurrentPath();
    $parameters = $this->request->query->all();
    $modalParameter = empty($parameters['modal']) ? FALSE : $parameters['modal'];

    $modalIds = $this->getModalIds($currentPath, $modalParameter);

    $modalStorage = $this->entityTypeManager->getStorage('modal_page_modal');

    foreach ($modalIds as $modalId) {

      $modal = $modalStorage->load($modalId);

      $modalToShow = ($modal->type->value === 'parameter') ?
        $this->getModalToShowByParameter($modal, $modalParameter) :
        $this->getModalToShowByPage($modal, $currentPath);

        if (!empty($modalToShow)) {
          return $modalToShow;
        }
    }

    return $modalToShow;
  }

  /**
   * Apply the filter on text.
   *
   * @param string $text
   *   The text for be cleared.
   *
   * @return string
   *   Return the text cleared.
   */
  public function clearText(string $text) :string {
    $text = Xss::filter($text, $this->getAllowTags());
    return trim($text);
  }

  /**
   * Get user name authenticate.
   *
   * @param string $text
   *   The body text of modal.
   *
   * @return string
   *   The text with user name or visitor text.
   */
  public function getAutheticatedUserName($text) :string {
    if ($this->currentUser->isAuthenticated()) {
      return str_replace('@user_name@', $this->currentUser->getAccountName(), $text);
    }
    return str_replace('@user_name@', $this->t('visitor'), $text);
  }

  /**
   * Get the current path.
   *
   * @return string
   *   The current path.
   */
  public function getCurrentPath() :string {
    $currentPath = ltrim($this->request->getRequestUri(), '/');
    if ($this->pathMatcher->isFrontPage()) {
      $currentPath = '<front>';
    }
    return $currentPath;
  }

  /**
   * Get the modal by page.
   *
   * @param object $modal
   *   The object modal.
   * @param string $currentPath
   *   The current path.
   *
   * @return object
   *   Retunr the modal.
   */
  public function getModalToShowByPage($modal, $currentPath) {
    $pages = $modal->pages->value;
    $pages = explode(PHP_EOL, $pages);

    foreach ($pages as $page) {

      $path = $page;
      if ($path != '<front>') {
        $path = Xss::filter($path);
      }

      $path = ltrim(trim($path), '/');
      if ($currentPath == $path || $path == NULL) {
        return $modal;
      }
    }
  }

  /**
   * Get the modal by parameter.
   *
   * @param object $modal
   *   The object modal.
   * @param string $modalParamenter
   *   The string text of parameters.
   *
   * @return bool
   *   Return modal or false.
   */
  public function getModalToShowByParameter($modal, $modalParamenter) {
    $parameters = $modal->parameters->value;
    $parameters = explode(PHP_EOL, $parameters);

    foreach ($parameters as $parameter) {
     $parameter = trim($parameter);
      if ($modalParamenter == $parameter) {
        return $modal;
      }
    }
    return FALSE;
  }

  /**
   * Allowed tags on modal page.
   *
   * @return array
   *   Return the tags allowed.
   */
  public function getAllowTags() :array {
    return [
      'h1', 'h2', 'a', 'b', 'big', 'code', 'del', 'em', 'i', 'ins', 'pre', 'q', 'small',
      'span', 'strong', 'sub', 'sup', 'tt', 'ol', 'ul', 'li', 'p', 'br', 'img',
    ];
  }

  /**
   * Get ids modal.
   *
   * @param string $currentPath
   *   Current path.
   * @param string $modalParameter
   *   Parameter for show modal.
   *
   * @return mixed
   *   Return ids list.
   */
  protected function getModalIds(string $currentPath, string $modalParameter) {
    $query = $this->entityTypeManager->getStorage('modal_page_modal')->getQuery();

    if ($modalParameter) {
      $query->condition('parameters', '%' . $modalParameter . '%', 'like');
    }
    else {
      $groupCondition = $query->orConditionGroup()
        ->condition('pages', '%' . $currentPath . '%', 'like')
        ->condition('pages', NULL, 'IS');
      $query->condition($groupCondition);
    }

    if (!empty($this->languageManager->getCurrentLanguage()->getId())) {
      $lang_code = $this->languageManager->getCurrentLanguage()->getId();
      $condition = $query->orConditionGroup()->condition('langcode', $lang_code, '=')->condition('langcode', '', '=');
      $query->condition($condition);
    }
    return $query->execute();
  }

  /**
   * Import Modal Config to Entity.
   */
  public function importModalConfigToEntity() {

    $language = $this->languageManager->getCurrentLanguage()->getId();

    $config = $this->configFactory->get('modal_page.settings');

    $modals = $config->get('modals');

    $modals_by_parameter = $config->get('modals_by_parameter');

    $allow_tags = $this->getAllowTags();

    if (empty($modals) && empty($modals_by_parameter)) {
      return FALSE;
    }

    if (!empty($modals)) {

      $modals_settings = explode(PHP_EOL, $modals);

      foreach ($modals_settings as $modal_settings) {

        $modal = explode('|', $modal_settings);

        $path = $modal[0];

        if ($path != '<front>') {
          $path = Xss::filter($modal[0]);
        }

        $path = trim($path);
        $path = ltrim($path, '/');

        $title = Xss::filter($modal[1], $allow_tags);
        $title = trim($title);

        $text = Xss::filter($modal[2], $allow_tags);
        $text = trim($text);

        $button = Xss::filter($modal[3]);
        $button = trim($button);

        $uuid = $this->uuidService->generate();

        $modal = [
          'uuid' => $uuid,
          'title' => $title,
          'body' => $text,
          'type' => 'page',
          'pages' => $path,
          'ok_label_button' => $button,
          'langcode' => $language,
          'created' => time(),
          'changed' => time(),
        ];

        $query = $this->database->insert('modal');
        $query->fields($modal);
        $query->execute();
      }
    }

    if (!empty($modals_by_parameter)) {

      $modals_settings = explode(PHP_EOL, $modals_by_parameter);

      foreach ($modals_settings as $modal_settings) {

        $modal = explode('|', $modal_settings);

        $parameter_settings = Xss::filter($modal[0]);

        $parameter = trim($parameter_settings);

        $parameter_data = explode('=', $parameter);

        $parameter_value = $parameter_data[1];

        $title = Xss::filter($modal[1], $allow_tags);
        $title = trim($title);

        $text = Xss::filter($modal[2], $allow_tags);
        $text = trim($text);

        $button = Xss::filter($modal[3]);
        $button = trim($button);

        $uuid = $this->uuidService->generate();

        $modal = [
          'uuid' => $uuid,
          'title' => $title,
          'body' => $text,
          'type' => 'parameter',
          'parameters' => $parameter_value,
          'ok_label_button' => $button,
          'langcode' => $language,
          'created' => time(),
          'changed' => time(),
        ];

        $query = $this->database->insert('modal');
        $query->fields($modal);
        $query->execute();

      }
    }
  }

}
