<?php

namespace Drupal\dfinance\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\dfinance\Entity\FinancialDocInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class FinancialDocController.
 *
 *  Returns responses for Financial Document routes.
 */
class FinancialDocController extends EntityController implements ContainerInjectionInterface {

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  private $route_match;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('renderer'),
      $container->get('string_translation'),
      $container->get('url_generator'),
      $container->get('current_route_match')
    );
  }

  /**
   * Constructs a new EntityController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current Route Match
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, RendererInterface $renderer, TranslationInterface $string_translation, UrlGeneratorInterface $url_generator, RouteMatchInterface $route_match) {
    $this->route_match = $route_match;
    parent::__construct($entity_type_manager, $entity_type_bundle_info, $entity_repository, $renderer, $string_translation, $url_generator);
  }

  /**
   * Helper function to get EntityTypeManager
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private function entityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function redirect($route_name, array $route_parameters = [], array $options = [], $status = 302) {
    if ($finance_organisation = $this->route_match->getRawParameter('finance_organisation')) {
      if ($route_name == 'entity.finance_doc.add_form') {
        $route_name = 'entity.finance_doc.add_form_for_organisation';
      }
      $route_parameters += [
        'finance_organisation' => $finance_organisation,
      ];
    }
    return parent::redirect($route_name, $route_parameters, $options, $status);
  }

  public function addPageForOrganisation($entity_type_id) {
    $add_page = $this->addPage($entity_type_id);

    $finance_organisation_id = $this->route_match->getRawParameter('finance_organisation');
    if ($finance_organisation_id== null) {
      return $add_page;
    }

    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $form_route_name_for_org = "entity.$entity_type_id.add_form_for_organisation";

    if ($bundle_entity_type_id = $entity_type->getBundleEntityType()) {
      $bundle_argument = $bundle_entity_type_id;
    } else {
      $bundle_argument = $entity_type->getKey('bundle');
    }

    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

    if ($add_page instanceof RedirectResponse) {
      return $this->redirect($form_route_name_for_org, [
        $bundle_argument => key($bundles),
        'finance_organisation' => $finance_organisation_id,
      ]);
    }

    foreach ($bundles as $bundle_name => $bundle_info) {
      $add_page['#bundles'][$bundle_name] = [
        'label' => $bundle_info['label'],
        'description' => isset($bundle_info['description']) ? $bundle_info['description'] : '',
        'add_link' => Link::createFromRoute($bundle_info['label'], $form_route_name_for_org, [
          $bundle_argument => $bundle_name,
          'finance_organisation' => $finance_organisation_id,
        ]),
      ];
    }

    return $add_page;
  }

  /**
   * Displays a Financial Document  revision.
   *
   * @param int $financial_doc_revision
   *   The Financial Document  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionShow($financial_doc_revision) {
    $financial_doc = $this->entityTypeManager()->getStorage('financial_doc')->loadRevision($financial_doc_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('financial_doc');

    return $view_builder->view($financial_doc);
  }

  /**
   * Page title callback for a Financial Document  revision.
   *
   * @param int $financial_doc_revision
   *   The Financial Document  revision ID.
   *
   * @return string
   *   The page title.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionPageTitle($financial_doc_revision) {
    $financial_doc = $this->entityTypeManager()->getStorage('financial_doc')->loadRevision($financial_doc_revision);
    return $this->t('Revision of %title from %date', ['%title' => $financial_doc->label(), '%date' => format_date($financial_doc->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Financial Document .
   *
   * @param \Drupal\dfinance\Entity\FinancialDocInterface $financial_doc
   *   A Financial Document  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionOverview(FinancialDocInterface $financial_doc) {
    $account = \Drupal::currentUser();
    $langcode = $financial_doc->language()->getId();
    $langname = $financial_doc->language()->getName();
    $languages = $financial_doc->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $financial_doc_storage = $this->entityTypeManager()->getStorage('financial_doc');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $financial_doc->label()]) : $this->t('Revisions for %title', ['%title' => $financial_doc->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all financial document revisions") || $account->hasPermission('administer financial document entities')));
    $delete_permission = (($account->hasPermission("delete all financial document revisions") || $account->hasPermission('administer financial document entities')));

    $rows = [];

    $vids = $financial_doc_storage->revisionIds($financial_doc);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dfinance\Entity\FinancialDocInterface $revision */
      $revision = $financial_doc_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $financial_doc->getRevisionId()) {
          $link = $this->l($date, new Url('entity.financial_doc.revision', ['financial_doc' => $financial_doc->id(), 'financial_doc_revision' => $vid]));
        }
        else {
          $link = $financial_doc->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.financial_doc.translation_revert', ['financial_doc' => $financial_doc->id(), 'financial_doc_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.financial_doc.revision_revert', ['financial_doc' => $financial_doc->id(), 'financial_doc_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.financial_doc.revision_delete', ['financial_doc' => $financial_doc->id(), 'financial_doc_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['financial_doc_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
