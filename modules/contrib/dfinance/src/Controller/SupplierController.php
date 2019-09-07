<?php

namespace Drupal\dfinance\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\dfinance\Entity\SupplierInterface;

/**
 * Class FinancialDocController.
 *
 *  Returns responses for Supplier routes.
 */
class SupplierController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Supplier revision.
   *
   * @param int $supplier_revision
   *   The Supplier revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionShow($supplier_revision) {
    $finance_supplier = $this->entityTypeManager()->getStorage('finance_supplier')->loadRevision($supplier_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('finance_supplier');

    return $view_builder->view($finance_supplier);
  }

  /**
   * Page title callback for a Supplier revision.
   *
   * @param int $supplier_revision
   *   The Supplier revision ID.
   *
   * @return string
   *   The page title.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionPageTitle($supplier_revision) {
    $finance_supplier = $this->entityTypeManager()->getStorage('finance_supplier')->loadRevision($finance_supplier_revision);
    return $this->t('Revision of %title from %date', ['%title' => $finance_supplier->label(), '%date' => format_date($finance_supplier->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Supplier.
   *
   * @param \Drupal\dfinance\Entity\SupplierInterface $finance_supplier
   *   A Supplier object.
   *
   * @return array
   *   An array as expected by drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionOverview(SupplierInterface $finance_supplier) {
    $account = $this->currentUser();
    $langcode = $finance_supplier->language()->getId();
    $langname = $finance_supplier->language()->getName();
    $languages = $finance_supplier->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $finance_supplier_storage = $this->entityTypeManager()->getStorage('finance_supplier');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $finance_supplier->label()]) : $this->t('Revisions for %title', ['%title' => $finance_supplier->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all Supplier revisions") || $account->hasPermission('administer Supplier entities')));
    $delete_permission = (($account->hasPermission("delete all Supplier revisions") || $account->hasPermission('administer Supplier entities')));

    $rows = [];

    $vids = $finance_supplier_storage->revisionIds($finance_supplier);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dfinance\Entity\SupplierInterface $revision */
      $revision = $finance_supplier_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $finance_supplier->getRevisionId()) {
          $link = $this->l($date, new Url('entity.finance_supplier.revision', ['finance_supplier' => $finance_supplier->id(), 'finance_supplier_revision' => $vid]));
        }
        else {
          $link = $finance_supplier->link($date);
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
              Url::fromRoute('entity.finance_supplier.translation_revert', ['finance_supplier' => $finance_supplier->id(), 'finance_supplier_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.finance_supplier.revision_revert', ['finance_supplier' => $finance_supplier->id(), 'finance_supplier_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.finance_supplier.revision_delete', ['finance_supplier' => $finance_supplier->id(), 'finance_supplier_revision' => $vid]),
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

    $build['finance_supplier_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
