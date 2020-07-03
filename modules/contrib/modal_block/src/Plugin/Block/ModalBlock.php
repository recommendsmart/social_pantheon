<?php

namespace Drupal\modal_block\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Context\LazyContextRepository;
use Drupal\Core\Block\BlockManager;

/**
 * Provides a 'ModalBlock' block class.
 *
 * @Block(
 *  id = "modal_block",
 *  admin_label = @Translation("Modal Block"),
 * )
 */
class ModalBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The context repository.
   *
   * @var \Drupal\Core\Plugin\Context\LazyContextRepository
   */
  protected $contextRepository;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Constructs a new ModalBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Plugin\Context\LazyContextRepository $context_repository
   *   The context repository.
   * @param \Drupal\Core\Block\BlockManager $block_manager
   *   The block manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LazyContextRepository $context_repository,
    BlockManager $block_manager
  ) {
    $this->contextRepository = $context_repository;
    $this->blockManager = $block_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('context.repository'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $modal_block_id = $this->configuration['modal_block_id'];
    $options = $this->prepareModalBlockOptions();
    // No need to check if block exist, because if not - blockAccess return forbidden.
    /** @var  $block_instance \Drupal\Core\Block\BlockPluginInterface */
    if ($options['block']['is_ajax']) {
      $build = [
        '#type' => 'link',
        '#title' => '',
        '#url' => Url::fromRoute(
          'modal_block.ajax_callback',
          ['block' => $modal_block_id, 'js' => 'ajax'],
          // Link options attributes precedence than render array attributes
          [
            'attributes' => [
              'class' => ['use-ajax', 'modal-block'],
              'data-dialog-type' => $options['dialog']['modal'] ? 'modal' : 'dialog',
              'data-dialog-options' => Json::encode($options['dialog']),
              'style' => 'display:none',
              'data-modal-block-id' => $modal_block_id,
            ],
          ]
        ),
      ];
      unset($options['dialog']);
      $build['#attached'] = [
        'library' => [
          'modal_block/modal_block',
          'core/drupal.ajax',
        ],
        'drupalSettings' => [
          'modal_block' => [
            $modal_block_id => $options,
          ],
        ],
      ];
    }
    elseif ($block_instance = $this->blockManager->createInstance($this->configuration['block']['id'], [])) {
      $build = [
        '#theme' => 'modal_block',
        '#modal_block_id' => $modal_block_id,
        '#content' => $block_instance->build(),
        '#attributes' => [
          'data-modal-block-id' => $modal_block_id,
          'class' => ['modal-block', 'hidden'],
        ],
        '#attached' => [
          'library' => ['modal_block/modal_block'],
          'drupalSettings' => [
            'modal_block' => [
              $modal_block_id => $options,
            ],
          ],
        ],
      ];
    }

    return $build;
  }

  /**
   * Prepare option for modal block.
   *
   * @return array
   */
  protected function prepareModalBlockOptions() {
    $options = $this->configuration;
    $options['block']['periodicity'] = $this->processPeriodicity($options['block']['periodicity']);
    $options['dialog']['position'] = [
      'my' => "{$options['dialog']['position']['my']['h']} {$options['dialog']['position']['my']['v']}",
      'at' => "{$options['dialog']['position']['at']['h']} {$options['dialog']['position']['at']['v']}",
    ];
    $options['dialog']['height'] = $options['dialog']['height'] ?: '100%';

    $options['cookie']['name'] = 'modal_block_' . $options['cookie']['name'];
    $options['cookie']['path'] = $options['cookie']['path'] ?: '';
    $options['block']['is_ajax'] = (bool) $options['block']['is_ajax'];
    $options['block']['first'] = intval($options['block']['first']);
    if ($options['label_display'] === 'visible') {
      $options['dialog']['title'] = $options['label'];
    }
    $default = $this->defaultModalBlockConfiguration();
    foreach ($options as $key => $value) {
      if (!isset($default[$key])) {
        unset($options[$key]);
      }
    }
    unset($options['block']['id']);
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if ($this->configuration['modal_block_id']) {
      if ($block_instance = $this->blockManager->createInstance($this->configuration['block']['id'], [])) {
        return $block_instance->access($account, TRUE);
      }
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return $this->defaultModalBlockConfiguration() + ['modal_block_id' => NULL] + parent::defaultConfiguration();
  }

  /**
   * Specified default Modal Block configuration.
   *
   * @return array
   */
  protected function defaultModalBlockConfiguration() {
    return [
      'block' => [
        'id' => NULL,
        'is_ajax' => FALSE,
        'event' => 'load',
        'delay' => 5000,
        'visit_duration' => 86400,
        'first' => 1,
        'periodicity' => '0',
      ],
      'cookie' => [
        'name' => 'default',
        'path' => '/',
        'expires' => 2592000,
      ],
      'dialog' => [
        'modal' => TRUE,
        'closeOnEscape' => TRUE,
        'position' => [
          'my' => [
            'h' => 'center',
            'v' => 'center',
          ],
          'at' => [
            'h' => 'center',
            'v' => 'center',
          ],
        ],
        'width' => 300,
        'height' => 0,
        'show' => [
          'effect' => 'slideDown',
          'duration' => 500,
        ],
        'hide' => [
          'effect' => 'slideUp',
          'duration' => 500,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Only add blocks which work without any available context.
    $definitions = $this->blockManager->getFilteredDefinitions('block_ui', $this->contextRepository->getAvailableContexts());
    // Order by category, and then by admin label.
    $definitions = $this->blockManager->getSortedDefinitions($definitions);
    // Filter out definitions that are not intended to be placed by the UI.
    $definitions = array_filter($definitions, function (array $definition) {
      return empty($definition['_block_ui_hidden']);
    });

    $options = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = $plugin_definition['admin_label'];
    }
    //$form['#tree'] = TRUE;
    $form['block'] = [
      '#type' => 'details',
      '#title' => $this->t('Block settings'),
    ];
    $form['block']['id'] = [
      '#type' => 'select',
      '#title' => $this->t('Select block to display in a modal dialog.'),
      '#default_value' => $this->configuration['block']['id'],
      '#options' => $options,
      '#weight' => 10,
      '#required' => TRUE,
      '#description' => $this->t('Selected block will be displayed in modal.'),
    ];
    $form['block']['is_ajax'] = [
      '#type' => 'radios',
      '#title' => $this->t('Rendering modal content:'),
      '#options' => [
        0 => $this->t('among with main content'),
        1 => $this->t('by ajax callback'),
      ],
      '#description' => $this->t(''),
      '#default_value' => $this->configuration['block']['is_ajax'],
      '#required' => TRUE,
    ];
    $form['block']['event'] = [
      '#type' => 'select',
      '#title' => $this->t('Appearance event'),
      '#options' => [
        'load' => $this->t('after page load'),
        'exit' => $this->t('before page exit'),
      ],
      '#description' => $this->t('Modal block appearance event.'),
      '#default_value' => $this->configuration['block']['event'],
      '#required' => TRUE,
    ];
    $form['block']['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay'),
      '#step' => 1,
      '#min' => 1,
      '#description' => $this->t('Delay in milliseconds before showing modal block. Set 0 to display block immediately after page will be loaded.'),
      '#default_value' => $this->configuration['block']['delay'],
      '#required' => TRUE,
    ];
    $form['block']['visit_duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Visit duration'),
      '#step' => 1,
      '#min' => 0,
      '#description' => $this->t('Visit duration in seconds before current visit expires. Set 0 to increase visit number after each page attendance.'),
      '#default_value' => $this->configuration['block']['visit_duration'],
      '#required' => TRUE,
    ];
    $form['block']['first'] = [
      '#type' => 'number',
      '#title' => $this->t('First appearance'),
      '#min' => 1,
      '#step' => 1,
      '#description' => $this->t('Page visit number for first time displaying modal.'),
      '#default_value' => $this->configuration['block']['first'],
      '#required' => TRUE,
    ];
    $form['block']['periodicity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Periodicity'),
      '#description' => $this->t("Periodicity of the modal appearance. Use cases:<br>
          1. put 0 for displaying modal only once, then modal will not showing up until cookie expires.<br>
          2. put single number N for displaying block each N page visit. For example - <em>2</em> for displaying modal each second page visit.<br>
          3. Enter a comma-separated list of numbers for custom page visits configuration. For example: <em>3, 5</em> - for displaying modal on third and fifth page visit.<br>
          Note: all numbers in this filed are relative to the initial <em>First appearance</em> number."),
      '#default_value' => $this->configuration['block']['periodicity'],
      '#required' => TRUE,
    ];
    $form['cookie'] = [
      '#type' => 'details',
      '#title' => $this->t('Cookie settings'),
      '#open' => FALSE,
    ];
    $form['cookie']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie name'),
      '#field_prefix' => 'modal_block_',
      '#description' => $this->t("Cookie name, which will set up for this modal block instance. Allow to share or separate cookie visits counter among different modal block instances. Note: changing this value will reset visits counter for all users."),
      '#default_value' => $this->configuration['cookie']['name'],
      '#required' => TRUE,
    ];
    $form['cookie']['path'] = [
      '#type' => 'radios',
      '#title' => $this->t('Cookie path:'),
      '#options' => [
        '0' => $this->t('current page'),
        '/' => $this->t('entire site'),
      ],
      '#description' => $this->t('Determine how to store cookie. Entire site - set up one cookie for entire site (any page visit will increase cookie visit). Current page - each page with modal block will have own visits and expire storage.'),
      '#default_value' => $this->configuration['cookie']['path'],
      '#required' => TRUE,
    ];
    $form['cookie']['expires'] = [
      '#type' => 'number',
      '#title' => $this->t('Expires'),
      '#step' => 1,
      '#min' => 0,
      '#description' => $this->t('Period in seconds after which cookie value expires and user will considering like new one. 0 - for a browser session cookie.'),
      '#default_value' => $this->configuration['cookie']['expires'],
      '#required' => TRUE,
    ];
    $form['dialog'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Dialog settings'),
    ];

    $form['dialog']['modal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Modal'),
      '#description' => $this->t('If checked, dialof will displaying as modal.'),
      '#default_value' => $this->configuration['dialog']['modal'],
    ];
    $form['dialog']['closeOnEscape'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Close on ESC'),
      '#description' => $this->t('Specifies whether the dialog should close when it has focus and the user presses the escape (ESC) key.'),
      '#default_value' => $this->configuration['dialog']['closeOnEscape'],
    ];
    $form['dialog']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#step' => 1,
      '#min' => 1,
      '#description' => $this->t('The width of the dialog in pixels.'),
      '#default_value' => $this->configuration['dialog']['width'],
      '#required' => TRUE,
    ];
    $form['dialog']['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#step' => 1,
      '#min' => 0,
      '#description' => $this->t('The height of the dialog in pixels. Enter 0 for "auto" value'),
      '#default_value' => $this->configuration['dialog']['height'],
      '#required' => TRUE,
    ];
    $position_h = [
      'left' => $this->t('left'),
      'center' => $this->t('center'),
      'right' => $this->t('right'),
    ];
    $position_v = [
      'top' => $this->t('top'),
      'center' => $this->t('center'),
      'bottom' => $this->t('bottom'),
    ];
    $form['dialog']['position'] = [
      '#type' => 'details',
      '#title' => $this->t('Dialog position'),
      '#attributes' => ['open' => FALSE],
    ];
    $form['dialog']['position']['help'] = [
      '#type' => 'item',
      '#input' => FALSE,
      '#markup' => $this->t('Follow this <a href="@link" target="_blank">link</a> for more information.', ['@link' => 'https://api.jqueryui.com/position/']),
    ];
    $form['dialog']['position']['my']['h'] = [
      '#type' => 'select',
      '#title' => $this->t('My horizontal'),
      '#options' => $position_h,
      '#required' => TRUE,
      '#default_value' => $this->configuration['dialog']['position']['my']['h'],
    ];
    $form['dialog']['position']['my']['v'] = [
      '#type' => 'select',
      '#title' => $this->t('My vertical'),
      '#options' => $position_v,
      '#required' => TRUE,
      '#default_value' => $this->configuration['dialog']['position']['my']['v'],
    ];
    $form['dialog']['position']['at']['h'] = [
      '#type' => 'select',
      '#title' => $this->t('At horizontal'),
      '#options' => $position_h,
      '#required' => TRUE,
      '#default_value' => $this->configuration['dialog']['position']['at']['h'],
    ];
    $form['dialog']['position']['at']['v'] = [
      '#type' => 'select',
      '#title' => $this->t('At vertical'),
      '#options' => $position_v,
      '#required' => TRUE,
      '#default_value' => $this->configuration['dialog']['position']['at']['v'],
    ];
    $form['dialog']['show'] = [
      '#type' => 'details',
      '#title' => $this->t('Show'),
      '#attributes' => ['open' => FALSE],
    ];
    $form['dialog']['show']['effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Effect'),
      '#options' => [
        'slideDown' => $this->t('Slide down'),
        'fadeIn' => $this->t('fadeIn'),
      ],
      '#description' => $this->t('Block appearance effect.'),
      '#default_value' => $this->configuration['dialog']['show']['effect'],
      '#required' => TRUE,
    ];
    $form['dialog']['show']['duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Effect duration'),
      '#step' => 1,
      '#min' => 1,
      '#description' => $this->t('Effect duration in milliseconds.'),
      '#default_value' => $this->configuration['dialog']['show']['duration'],
      '#required' => TRUE,
    ];
    $form['dialog']['hide'] = [
      '#type' => 'details',
      '#title' => $this->t('Hide'),
      '#attributes' => ['open' => FALSE],
      '#tree' => TRUE,
    ];
    $form['dialog']['hide']['effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Effect'),
      '#options' => [
        'slideUp' => $this->t('Slide up'),
        'fadeOut' => $this->t('fadeOut'),
      ],
      '#description' => $this->t('Block hiding effect.'),
      '#default_value' => $this->configuration['dialog']['hide']['effect'],
      '#required' => TRUE,
    ];
    $form['dialog']['hide']['duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Effect duration'),
      '#step' => 1,
      '#min' => 1,
      '#description' => $this->t('Effect duration in milliseconds.'),
      '#default_value' => $this->configuration['dialog']['hide']['duration'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $raw = explode(',', $form_state->getValue(['block', 'periodicity']));
    $processed = $this->processPeriodicity($raw);
    if (count($raw) !== count($processed)) {
      $form_state->setErrorByName('block][periodicity', $this->t('Visits: required field <em>Visits</em> having wrong format .'));
    }
    else {
      $form_state->setValue([
        'block',
        'periodicity',
      ], implode(', ', $processed));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save block and checkbox values to our configuration.
    $values = $form_state->getValues();
    $default = $this->defaultModalBlockConfiguration();
    foreach ($default as $key => $item) {
      $this->configuration[$key] = array_replace_recursive($item, $values[$key]);
    }
    if ($form_state instanceof SubformStateInterface) {
      $all_values = $form_state->getCompleteFormState()->getValues();
      $this->configuration['modal_block_id'] = $all_values['id'] ?? NULL;
    }
  }

  /**
   * Process modal block periodicity string.
   *
   * @param $raw
   *
   * @return array
   */
  protected function processPeriodicity($raw) {
    $raw = \is_array($raw) ? $raw : explode(',', $raw);
    $processed = array_map(function ($visit) {
      $visit = trim($visit);
      return ctype_digit(strval($visit)) ? intval($visit) : NULL;
    }, $raw);
    return array_filter($processed, '\is_int');
  }

}
