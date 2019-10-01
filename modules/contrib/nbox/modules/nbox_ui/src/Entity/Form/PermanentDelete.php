<?php

namespace Drupal\nbox_ui\Entity\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Confirmation form to appear when permanently deleting messages from trash.
 *
 * @package Drupal\nbox_ui\Entity\Form
 */
class PermanentDelete extends ConfirmFormBase {

  /**
   * Contains the calling view ID.
   *
   * @var string
   */
  protected $viewId;

  /**
   * Contains the calling display ID.
   *
   * @var string
   */
  protected $displayId;

  /**
   * The tempstore object associated with the current view.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempstore;

  /**
   * The tempstore data for the current user.
   *
   * @var array
   */
  protected $tempstoreData;

  /**
   * VBO action processor.
   *
   * @var \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionProcessor
   */
  protected  $actionProcessor;

  /**
   * {@inheritdoc}
   */
  public function __construct(PrivateTempStoreFactory $privateTempStore, ViewsBulkOperationsActionProcessor $actionProcessor) {
    $this->viewId = $this->getRequest()->get('view_id');
    $this->displayId = $this->getRequest()->get('display_id');
    $tempStoreName = 'views_bulk_operations_' . $this->viewId . '_' . $this->displayId;
    if ($this->tempstore = $privateTempStore->get($tempStoreName)) {
      $this->tempstoreData = $this->tempstore->get($this->currentUser()->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('views_bulk_operations.processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nbox_ui_permanent_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if ($this->tempstoreData['display_id'] === 'page_1' && isset($this->tempstoreData['arguments'][0]) && $this->tempstoreData['arguments'][0] === 'trash') {
      return parent::buildForm($form, $form_state);
    }
    $this->executeAction();
    // As there is no form submission, use normal redirect.
    $url = Url::fromRoute("view.$this->viewId.$this->displayId")->toString();
    return new RedirectResponse($url);
  }

  /**
   * Executes the delete action.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function executeAction() {
    $this->actionProcessor->executeProcessing($this->tempstoreData);
    $this->tempstore->delete($this->currentUser()->id());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->executeAction();
    $this->messenger()->deleteAll();
    $message = $this->getStringTranslation()->formatPlural(
      count($this->tempstoreData['list']),
      'The message has been permanently deleted.',
      'The messages have been permanently deleted'
    );
    $this->messenger()->addMessage($message);
    // Use the form redirect.
    $form_state->setRedirect("view.$this->viewId.$this->displayId");
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if (isset($this->tempstoreData['arguments']['0']) && $this->tempstoreData['arguments']['0'] === 'trash') {
      return $this->getStringTranslation()->formatPlural(
        count($this->tempstoreData['list']),
        'Are you sure you want to permanently delete this message?',
        'Are you sure you want to permanently delete these messages?'
      );
    }
    return $this->getStringTranslation()->formatPlural(
      count($this->tempstoreData['list']),
      'Are you sure you want to move this message to the trash?',
      'Are you sure you want to move these messages to the trash?'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute("view.$this->viewId.$this->displayId");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if (isset($this->tempstoreData['arguments']['0']) && $this->tempstoreData['arguments']['0'] === 'trash') {
      return $this->t('Delete');
    }
    return $this->t('Move');
  }

}
