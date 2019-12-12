<?php

namespace Drupal\dashboards_matomo\Plugin\Dashboard;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\dashboards\Plugin\Dashboard\ChartTrait;
use Drupal\matomo_reporting_api\MatomoQueryFactory;
use Drupal\dashboards\Plugin\DashboardLazyBuildBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for matomo plugins.
 */
abstract class MatomoBase extends DashboardLazyBuildBase {
  use ChartTrait;

  /**
   * Entity query.
   *
   * @var \Drupal\matomo_reporting_api\MatomoQueryFactory
   */
  protected $matomoQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $cache, MatomoQueryFactory $matomo) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $cache);
    $this->matomoQuery = $matomo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dashboards.cache'),
      $container->get('matomo.query_factory')
    );
  }

  /**
   * Get matomo query.
   *
   * @return \Drupal\matomo_reporting_api\MatomoQueryFactory
   *   Matomo query factory.
   */
  public function getQuery() {
    return $this->matomoQuery;
  }

  /**
   * Translate date matomo string.
   *
   * @param string $period
   *   Period to translated.
   *
   * @return string
   *   Translated date.
   */
  protected function getDateTranslated(string $period): string {
    $format = 'Y-m-d';
    $time = time();
    switch ($period) {
      case 'last_seven_days':
        $time = strtotime('-7 days');
        break;

      case 'this_week':
        $time = strtotime('monday this week');
        break;

      case 'this_month':
        $time = strtotime('first day of this month');
        break;

      case 'last_three_months':
        $time = strtotime('first day of this month -3 months');
        break;

      case 'year':
        $time = strtotime('first day of this month -1 year');
        break;

      default:
        return $period;
    }
    $date = new \DateTime();
    $date->setTimestamp($time);
    $now = new \DateTime();
    return implode(',', [
      $date->format($format),
      $now->format($format),
    ]);
  }

  /**
   * Helper function for build rows from matomo.
   *
   * @param mixed $response
   *   Data from matomo.
   * @param string $label
   *   Label for display.
   * @param array $column
   *   Columns to show.
   */
  protected function buildDateRows($response, $label, array $column) {
    $labels = [$label];
    foreach ($response as $date => &$row) {
      foreach ($row as $key => $r) {
        $labels[$r['label']] = $r['label'];
        unset($row[$key]);
        $row[$r['label']] = $r;
        uksort($row, function ($a, $b) {
          return strcmp($a, $b);
        });
      }
    }
    $items = [];
    foreach ($response as $date => &$row) {
      $item = [$date];
      if (empty($row)) {
        if (is_array($column)) {
          foreach ($column as $c) {
            $item[] = 0;
          }
          continue;
        }
        $item[] = 0;
      }
      foreach ($row as $r) {
        if (is_array($column)) {
          foreach ($column as $c) {
            $item[] = $r[$c];
          }
          continue;
        }
        $item[] = $r[$column];
      }
      $items[] = $item;
    }
    $this->setRows($items);
    $this->setLabels($labels);
  }

  /**
   * Helper function for query matomo.
   *
   * @param string $action
   *   Matomo action to call.
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Response array
   */
  protected function query($action, array $params): array {
    $cid = md5(serialize([$action, $params]));
    if ($data = $this->getCache($cid)) {
      return $data->data;
    }
    $query = $this->matomoQuery->getQuery($action);
    $query->setParameters($params);

    $response = $query->execute()->getRawResponse();
    $response = Json::decode($response->getBody()->getContents());
    if (isset($response['result']) && $response['result'] == 'error') {
      throw new \Exception($response['message']);
    }
    $this->setCache($cid, $response, time() + 600);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSettingsForm(array $form, FormStateInterface $form_state, array $configuration): array {
    $form['period'] = [
      '#type' => 'select',
      '#options' => [
        'day' => $this->t('Day'),
        'week' => $this->t('Week'),
        'month' => $this->t('Month'),
        'year' => $this->t('Year'),
      ],
      '#default_value' => (isset($configuration['period'])) ? $configuration['period'] : 'day',
    ];
    $form['date'] = [
      '#type' => 'select',
      '#options' => [
        'last_seven_days' => $this->t('Last seven days'),
        'this_week' => $this->t('This week'),
        'this_month' => $this->t('This month'),
        'last_three_months' => $this->t('Last 3 months'),
        'last_six_months' => $this->t('Last 6 months'),
        'year' => $this->t('This year'),
      ],
      '#default_value' => (isset($configuration['date'])) ? $configuration['date'] : 'today',
    ];
    $form['chart_type'] = [
      '#type' => 'select',
      '#options' => $this->getAllowedStyles(),
      '#default_value' => (isset($configuration['chart_type'])) ? $configuration['chart_type'] : 'bar',
    ];
    return $form;
  }

}
