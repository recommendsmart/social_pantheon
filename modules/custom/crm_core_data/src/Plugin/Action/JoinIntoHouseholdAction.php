<?php

namespace Drupal\crm_core_data\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crm_core_data\Entity\Contact;
use Drupal\relation\Entity\Relation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityFormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Merges 2 or more datas into household data.
 *
 * @Action(
 *   id = "join_into_household_action",
 *   label = @Translation("Join into household"),
 *   type = "crm_core_data"
 * )
 */
class JoinIntoHouseholdAction extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * A default relation type for household relations.
   */
  const RELATION_TYPE_HOUSEHOLD = 'crm_member';

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The entity form bulder service.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * Constructs a EmailAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user service.
   * @param \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder
   *   The entity form builder service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxy $current_user, EntityFormBuilder $entity_form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('current_user'),
      $container->get('entity.form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $return_as_object ? AccessResult::allowed() : AccessResult::allowed()->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    if (!isset($this->configuration['household'])) {
      $this->configuration['household'] = Data::create(['type' => 'household']);
    }
    // Saving household only now because user can click "Cancel" on confirmation
    // page(if he/she will notice that selected wrong datas).
    $this->configuration['household']->setOwnerId($this->currentUser->id());
    $this->configuration['household']->save();
    foreach ($objects as $member) {
      $endpoints = array(
        0 => array(
          'entity_type' => $member->getEntityTypeId(),
          'entity_id' => $member->id(),
        ),
        1 => array(
          'entity_type' => 'crm_core_data',
          'entity_id' => $this->configuration['household']->id(),
        ),
      );
      $relation = Relation::create(['relation_type' => self::RELATION_TYPE_HOUSEHOLD, 'endpoints' => $endpoints]);
      $relation->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple(array($object));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $household = Data::create(['type' => 'household', 'name' => 'Fam. Smith']);
    return $this->entityFormBuilder->getForm($household);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['household'] = $form_state->getFormObject()->getEntity();
  }

}
