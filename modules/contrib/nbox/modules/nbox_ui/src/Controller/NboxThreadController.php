<?php

namespace Drupal\nbox_ui\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\nbox\Entity\Nbox;
use Drupal\nbox\Entity\NboxThread;
use Symfony\Component\HttpFoundation\Response;
use Drupal\nbox_ui\Form\ThreadActionForm;

/**
 * Provides Nbox thread metadata route controllers.
 */
class NboxThreadController extends ControllerBase {

  /**
   * Builds an Nbox thread UI.
   *
   * @param \Drupal\nbox\Entity\NboxThread $nbox_thread
   *   Nbox Thread.
   *
   * @return array
   *   Render array.
   */
  public function build(NboxThread $nbox_thread) {
    $messagesLoaded = $nbox_thread->getMessagesLoaded();
    $messagesRendered = $this->entityTypeManager()
      ->getViewBuilder('nbox')
      ->viewMultiple($messagesLoaded, 'thread_view');

    /** @var \Drupal\nbox\Entity\NboxMetadata $metadata */
    $metadata = $this->entityTypeManager()
      ->getStorage('nbox_metadata')
      ->loadByParticipantInThread($this->currentUser(), $nbox_thread);
    $metadata->setRead(TRUE);
    $metadata->save();
    $build = [
      '#theme' => 'nbox_thread',
      '#nbox_thread' => $nbox_thread,
      '#actions' => $this->formBuilder()->getForm(ThreadActionForm::class, $metadata),
      '#messages' => $messagesRendered,
      '#reply_wrapper' => [
        '#type' => 'container',
      ],
    ];
    $buttons = [
      'reply' => 'Reply',
      'reply_all' => 'Reply all',
      'forward' => 'Forward',
    ];

    foreach ($buttons as $type => $button) {
      $build['#reply_wrapper'][$type] = [
        '#type' => 'link',
        '#title' => $button,
        '#url' => Url::fromRoute('nbox_ui.thread_reply', [
          'nojs' => 'nojs',
          'nbox' => $metadata->getMostRecentId(),
          'type' => $type,
        ]),
        '#attached' => ['library' => ['core/drupal.ajax']],
        '#options' => [
          'attributes' => [
            'class' => [
              'button',
              'button--primary',
              'use-ajax',
            ],
          ],
        ],
      ];
    }

    return $build;
  }

  /**
   * Builds a reply form.
   *
   * @param string $nojs
   *   No JS.
   * @param \Drupal\nbox\Entity\Nbox|null $nbox
   *   Nbox message.
   * @param string $type
   *   Reply type (reply, reply_all or forward).
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\Response
   *   Response with form.
   */
  public function buildReply($nojs = 'nojs', Nbox $nbox = NULL, string $type) {
    switch ($type) {
      case 'reply':
        $reply = Nbox::replyTo($nbox);
        break;

      case 'reply_all':
        $reply = Nbox::replyToAll($nbox);
        break;

      case 'forward':
        $reply = Nbox::forward($nbox);
        break;
    }

    $form = $this->entityFormBuilder()->getForm($reply);
    if ($nojs === 'ajax') {
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#reply', $form));
      return $response;
    }
    $response = new Response($form);
    return $response;
  }

}
