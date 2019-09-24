<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Account Code revision.
 *
 * @ingroup dfinance
 */
class AccountCodeRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The Account Code revision.
   *
   * @var \Drupal\dfinance\Entity\AccountCodeInterface
   */
  protected $revision;

  /**
   * The Account Code storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $AccountCodeEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new AccountCodeEntityRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->AccountCodeEntityStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('financial_account_code'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'financial_account_code_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.financial_account_code.version_history', ['financial_account_code' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $financial_account_code_revision = NULL) {
    $this->revision = $this->AccountCodeEntityStorage->loadRevision($financial_account_code_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->AccountCodeEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Account Code: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Account Code %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.financial_account_code.canonical',
       ['financial_account_code' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {financial_account_code_field_revision} WHERE code = :code', [':code' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.financial_account_code.version_history',
         ['financial_account_code' => $this->revision->id()]
      );
    }
  }

}
