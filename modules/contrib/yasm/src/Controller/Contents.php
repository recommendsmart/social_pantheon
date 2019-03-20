<?php

namespace Drupal\yasm\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\yasm\Services\EntitiesStatisticsInterface;
use Drupal\yasm\Utility\YasmUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * YASM Statistics site contents controller.
 */
class Contents extends ControllerBase {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entities statitistics service.
   *
   * @var \Drupal\yasm\Services\EntitiesStatisticsInterface
   */
  protected $entitiesStatistics;

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(AccountInterface $account) {
    return ($this->moduleHandler->moduleExists('node')) ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * Site content page output.
   */
  public function siteContent(Request $request) {
    $filter = [];

    // Content creators ranking.
    $year = strtotime('first day of this year');
    $month = strtotime('first day of this month');
    $filters = [
      'overall' => [],
      'year' => [
        [
          'key'      => 'created',
          'value'    => $year,
          'operator' => '>=',
        ],
      ],
      'month' => [
        [
          'key'      => 'created',
          'value'    => $month,
          'operator' => '>=',
        ],
      ],
    ];
    $labels = [
      'overall' => $this->t('Overall creators'),
      'year'    => $this->t('@year creators', ['@year' => date('Y', $year)]),
      'month'   => $this->t('@month creators', ['@month' => date('F Y', $month)]),
    ];
    $rankings = [];
    foreach ($filters as $filter_key => $filter) {
      $data = $this->entitiesStatistics->aggregate('node', ['nid' => 'COUNT'], 'uid', $filter);
      $rows = [];
      foreach ($data as $value) {
        $user = $this->entityTypeManager->getStorage('user')->load($value['uid']);
        $rows[] = [
          'user' => $user->getDisplayName(),
          'count' => $value['nid_count'],
        ];
      }

      $rankings[] = [
        YasmUtility::title($labels[$filter_key], 'fas fa-crown'),
        YasmUtility::table([
          $this->t('User'),
          $this->t('Node count'),
        ], $rows),
      ];
    }
    $build_rankings = YasmUtility::columns($rankings, ['yasm-creators'], 3);

    // Count contents.
    $year = $request->query->get('year', 'all');
    if (is_numeric($year)) {
      $filter = YasmUtility::getYearFilter('created', $year);
    }

    $first_content_date = $this->entitiesStatistics->getFirstDateContent('node');
    $build['tabs'] = YasmUtility::getYearLinks(date('Y', $first_content_date), $year);
    $build['data'] = array_merge($this->buildContent($year, $filter), $build_rankings);

    return $build;
  }

  /**
   * My content page output.
   */
  public function myContent(Request $request) {
    // Filter contet by current user authoring.
    $filter = ['uid' => $this->currentUser->id()];

    $year = $request->query->get('year', 'all');
    if (is_numeric($year)) {
      $filter += YasmUtility::getYearFilter('created', $year);
    }

    $first_content_date = $this->entitiesStatistics->getFirstDateContent('node');
    $build['tabs'] = YasmUtility::getYearLinks(date('Y', $first_content_date), $year);
    $build['data'] = $this->buildContent($year, $filter);
    // Add user cache context because this can change for every user.
    $build['#cache']['contexts'] = ['user'];

    return $build;
  }

  /**
   * Build content page html.
   */
  private function buildContent($year, $conditions = []) {
    $entity = $this->entityTypeManager->getDefinition('node');

    if (!empty($entity)) {
      $info = $this->entitiesStatistics->getEntityAndBundlesInfo($entity, $conditions);
      if (isset($conditions['uid'])) {
        $this->messenger->addMessage($this->t('Statistics filtered with content authored by @user.', [
          '@user' => $this->currentUser->getDisplayName(),
        ]));
      }

      // Build total nodes by content type table.
      if (!empty($info['node']['bundles'])) {
        $rows = [];
        $rows[] = [
          'data' => [$this->t('Total'), $info['node']['count']],
          'class' => ['total-row'],
        ];
        foreach ($info['node']['bundles'] as $bundle) {
          $rows[] = [
            'data' => [$bundle['label'], $bundle['count']],
          ];
        }
        $table_nodes_by_ctype = YasmUtility::table([
          $this->t('Type'),
          $this->t('Count'),
        ], $rows, 'node_types');

        // Build nodes created/updated monthly by content type table.
        $dates = YasmUtility::getLastMonths($year);
        $bundles = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

        $labels = [];
        $data_created = $data_updated = [];
        // Collect data for all cols.
        foreach ($dates as $date) {
          // Add data col label.
          $labels[] = $date['label'];

          // Filter data.
          $filter_created = YasmUtility::getIntervalFilter('created', $date['max'], $date['min']);
          $filter_updated = YasmUtility::getIntervalFilter('changed', $date['max'], $date['min']);

          // Total nodes created by month.
          if (!isset($data_created['total_created']['label'])) {
            $data_created['total_created']['label'] = [
              'data' => $this->t('Total created'),
              'class' => ['total-row'],
            ];
          }
          $filters = array_merge($filter_created, $conditions);
          $data_created['total_created']['count'][$date['label']] = [
            'data' => $this->entitiesStatistics->count('node', $filters),
            'class' => ['total-row'],
          ];

          // Total nodes updated by month.
          if (!isset($data_updated['total_updated']['label'])) {
            $data_updated['total_updated']['label'] = [
              'data' => $this->t('Total updated'),
              'class' => ['total-row'],
            ];
          }
          $filters = array_merge($filter_updated, $conditions);
          $data_updated['total_updated']['count'][$date['label']] = [
            'data' => $this->entitiesStatistics->count('node', $filters),
            'class' => ['total-row'],
          ];

          // Nodes created/updated by type.
          foreach ($bundles as $key => $bundle) {
            $filter_bundle = ['type' => $key];

            // Key content type nodes created by month.
            $array_key = 'bundle_' . $key;
            // Created.
            if (!isset($data_created[$array_key]['label'])) {
              $data_created[$array_key]['label'] = $bundle->label();
            }
            $filters = array_merge($filter_created, $filter_bundle, $conditions);
            $data_created[$array_key]['count'][$date['label']] = $this->entitiesStatistics->count('node', $filters);
            // Updated.
            if (!isset($data_updated[$array_key]['label'])) {
              $data_updated[$array_key]['label'] = $bundle->label();
            }
            $filters = array_merge($filter_updated, $filter_bundle, $conditions);
            $data_updated[$array_key]['count'][$date['label']] = $this->entitiesStatistics->count('node', $filters);
          }
        }

        $rows = [];
        foreach ($data_created as $item) {
          $row = [];
          $row[] = $item['label'];
          if (!empty($item['count'])) {
            foreach ($item['count'] as $date_row) {
              $row[] = $date_row;
            }
          }
          $rows[] = $row;
        }
        $table_created_monthly = YasmUtility::table(array_merge([$this->t('Created')], $labels), $rows, 'nodes_created_monthly');

        $rows = [];
        foreach ($data_updated as $item) {
          $row = [];
          $row[] = $item['label'];
          if (!empty($item['count'])) {
            foreach ($item['count'] as $date_row) {
              $row[] = $date_row;
            }
          }
          $rows[] = $row;
        }
        $table_updated_monthly = YasmUtility::table(array_merge([$this->t('Updated')], $labels), $rows, 'nodes_updated_monthly');

        // Render content output.
        $build = [];

        $build[] = YasmUtility::title($this->t('Nodes by content type'), 'far fa-file-alt');
        $build[] = $table_nodes_by_ctype;

        $build[] = YasmUtility::title($this->t('Nodes created/updated monthly'), 'far fa-file-alt');
        $build[] = $table_created_monthly;
        $build[] = $table_updated_monthly;

        $build[] = [
          '#attached' => [
            'library' => ['yasm/global', 'yasm/fontawesome', 'yasm/datatables'],
          ],
          '#cache' => [
            'tags' => ['node_list'],
          ],
        ];

        return $build;
      }
    }

    return ['#markup' => $this->t('No data found.')];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, ModuleHandlerInterface $module_handler, EntitiesStatisticsInterface $entities_statistics) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->moduleHandler = $module_handler;
    $this->entitiesStatistics = $entities_statistics;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('module_handler'),
      $container->get('yasm.entities_statistics')
    );
  }

}
