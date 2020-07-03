<?php

namespace Drupal\modal_page\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for Modal entity.
 */
class ModalListBuilder extends EntityListBuilder {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('language_manager'),
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new ModalListBuilder object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(LanguageManagerInterface $language_manager, EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    $this->languageManager = $language_manager;
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $modal_page) {
    $operations = parent::getDefaultOperations($modal_page);
    $operations['published'] = [
      'title' => $this->t('Toggle Published'),
      'weight' => 15,
      'url' => $this->ensureDestination($modal_page->toUrl('published-form')),
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['langcode'] = $this->t('Language');
    $header['pages'] = $this->t('Pages');
    $header['parameters'] = $this->t('Parameters');
    $header['size'] = $this->t('Modal Size');
    $header['delay'] = $this->t('Delay');
    $header['published'] = $this->t('Published');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id->value;
    $row['title'] = $entity->title->value;
    $row['langcode'] = $this->getLanguageLabel($entity->langcode->value);

    if ($entity->type->value === 'parameter') {
      $row['pages'] = 'N/A';
      $row['parameters'] = $this->getParameters($entity->parameters->value);
    }
    else {
      $row['pages'] = $this->getPages($entity->pages->value);
      $row['parameters'] = 'N/A';
    }

    $row['size'] = $entity->modal_size
      ->getSetting('allowed_values')[$this->getColValue($entity->modal_size->value)];
    $row['delay_display'] = $this->getColValue($entity->delay_display->value, '0s');
    $row['published'] = $entity->published->value ? 'Yes' : 'No';

    return $row + parent::buildRow($entity);
  }

  /**
   * Get the value.
   *
   * @param mixed $value
   *   The some value.
   * @param mixed $default
   *   Values for empty case.
   *
   * @return string
   *   Return valeu.
   */
  public function getColValue($value, $default = 'N/A') {
    if (!empty($value)) {
      return $value;
    }
    return $default;
  }

  /**
   * Get the pages.
   *
   * @param string $pages
   *   Text with pages.
   * @param mixed $default
   *   Values for empty case.
   *
   * @return string
   *   Return list pages.
   */
  public function getPages($pages, $default = 'N/A') {
    $pages_value = '';
    $pages = explode(PHP_EOL, $pages);

    if (empty($pages)) {
      return $default;
    }

    foreach ($pages as $key => $page) {
      $pages_value .= ($key !== 0) ? ', ' : '';
      $pages_value .= trim($page);
    }

    return !strlen($pages_value) > 44 ?: substr($pages_value, 0, 44) . ' ...';
  }

  /**
   * Get the parameters.
   *
   * @param string $parameters
   *   Text with parameters.
   * @param mixed $default
   *   Values for empty case.
   *
   * @return string
   *   Return list pages.
   */
  public function getParameters($parameters, $default = 'N/A') {
    $parameters_value = '';
    $parameters = explode(PHP_EOL, $parameters);

    if (empty($parameters)) {
      return $default;
    }

    foreach ($parameters as $key => $parameter) {
      $parameters_value .= ($key !== 0) ? ', ' : '';
      $parameters_value .= 'modal=' . trim($parameter);
    }

    return !strlen($parameters_value) > 44 ?: substr($parameters_value, 0, 44) . ' ...';
  }

  /**
   * Get the language label.
   *
   * @param string $languageCode
   *   The code of language.
   * @param mixed $default
   *   Values for empty case.
   *
   * @return string
   *   Return label language.
   */
  public function getLanguageLabel($languageCode, $default = '- Any -') {
    if (empty($languageCode)) {
      return $default;
    }
    $languages = $this->languageManager->getLanguages();

    return empty($languages[$languageCode]->getName()) ? $default : $languages[$languageCode]->getName();
  }

}
