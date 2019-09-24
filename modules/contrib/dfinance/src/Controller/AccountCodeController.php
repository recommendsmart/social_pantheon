<?php

namespace Drupal\dfinance\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\dfinance\Entity\AccountCodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AccountCodeEntityController.
 *
 *  Returns responses for Account Code routes.
 */
class AccountCodeController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new AccountCodeEntityController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  public function collection() {
    $view = views_embed_view('financial_account_codes', 'embed');
    if ($view == NULL) {
      return [
        '#markup' => $this->t('Unable to display list of Account Codes because the View %view and display %display were not found.', [
          '%view' => 'financial_account_codes',
          '%display' => 'embed'
        ])
      ];
    }
    else {
      return $view;
    }
  }

  /**
   * Displays a Account Code revision.
   *
   * @param int $financial_account_code_revision
   *   The Account Code revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($financial_account_code_revision) {
    $financial_account_code = $this->entityTypeManager()->getStorage('financial_account_code')
      ->loadRevision($financial_account_code_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('financial_account_code');

    return $view_builder->view($financial_account_code);
  }

  /**
   * Page title callback for a Account Code revision.
   *
   * @param int $financial_account_code_revision
   *   The Account Code revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($financial_account_code_revision) {
    $financial_account_code = $this->entityTypeManager()->getStorage('financial_account_code')
      ->loadRevision($financial_account_code_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $financial_account_code->label(),
      '%date' => $this->dateFormatter->format($financial_account_code->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Account Code.
   *
   * @param \Drupal\dfinance\Entity\AccountCodeInterface $financial_account_code
   *   A Account Code object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AccountCodeInterface $financial_account_code) {
    $account = $this->currentUser();
    $financial_account_code_storage = $this->entityTypeManager()->getStorage('financial_account_code');

    $build['#title'] = $this->t('Revisions for %title', ['%title' => $financial_account_code->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all account code revisions") || $account->hasPermission('administer account code entities')));
    $delete_permission = (($account->hasPermission("delete all account code revisions") || $account->hasPermission('administer account code entities')));

    $rows = [];

    $vids = $financial_account_code_storage->revisionIds($financial_account_code);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dfinance\Entity\AccountCodeInterface $revision */
      $revision = $financial_account_code_storage->loadRevision($vid);
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $financial_account_code->getRevisionId()) {
          $link = $this->l($date, new Url('entity.financial_account_code.revision', [
            'financial_account_code' => $financial_account_code->id(),
            'financial_account_code_revision' => $vid,
          ]));
        }
        else {
          $link = $financial_account_code->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
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
              'url' => Url::fromRoute('entity.financial_account_code.revision_revert', [
                'financial_account_code' => $financial_account_code->id(),
                'financial_account_code_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.financial_account_code.revision_delete', [
                'financial_account_code' => $financial_account_code->id(),
                'financial_account_code_revision' => $vid,
              ]),
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

    $build['financial_account_code_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
