<?php

namespace Drupal\dfinance\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Supplier revision.
 *
 * @ingroup dfinance
 */
class SupplierRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The Supplier revision.
   *
   * @var \Drupal\dfinance\Entity\SupplierInterface
   */
  protected $revision;

  /**
   * The Supplier storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $supplierStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
  private $dateFormatter;

  /**
   * Constructs a new FinancialDocRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface
   *   A date formatter
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection, DateFormatterInterface $dateFormatter) {
    $this->supplierStorage = $entity_storage;
    $this->connection = $connection;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('finance_supplier'),
      $container->get('database'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'finance_supplier_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => format_date($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.finance_supplier.version_history', ['finance_supplier' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $finance_supplier_revision = NULL) {
    $this->revision = $this->supplierStorage->loadRevision($finance_supplier_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->supplierStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Supplier: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage($this->t('Revision from %revision-date of Supplier %title has been deleted.', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.finance_supplier.canonical',
       ['finance_supplier' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {finance_supplier_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.finance_supplier.version_history',
         ['finance_supplier' => $this->revision->id()]
      );
    }
  }

}
