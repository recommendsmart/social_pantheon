<?php

namespace Drupal\nbox\Entity\Views;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Nbox metadata entities.
 */
class NboxMetadataViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['nbox_metadata']['sender_summary'] = [
      'title' => $this->t('Sender summary'),
      'help' => $this->t('A sender summary plus message count.'),
      'field' => [
        'id' => 'nbox_view_sender_summary',
      ],
    ];

    $data['nbox_metadata']['recipient_summary'] = [
      'title' => $this->t('Recipient summary'),
      'help' => $this->t('A recipient summary plus message count.'),
      'field' => [
        'id' => 'nbox_view_recipient_summary',
      ],
    ];

    $data['nbox_metadata']['star_action'] = [
      'title' => $this->t('Star / unstar'),
      'field' => [
        'id' => 'nbox_view_star_action',
      ],
    ];

    $data['nbox_metadata']['subject'] = [
      'title' => $this->t('Subject'),
      'field' => [
        'id' => 'nbox_view_subject',
      ],
    ];

    $data['nbox_metadata']['relative_date'] = [
      'title' => $this->t('Relative date'),
      'field' => [
        'id' => 'nbox_view_relative_date',
      ],
      'sort' => [
        'id' => 'nbox_view_relative_date_sort',
      ],
    ];

    $data['nbox_metadata']['mailbox'] = [
      'title' => $this->t('Mailbox'),
      'help' => $this->t('Filter metadata by mailbox'),
      'filter' => [
        'id' => 'nbox_view_mailbox',
      ],
      'argument' => [
        'id' => 'nbox_view_mailbox_argument',
      ],
    ];

    $data['nbox_metadata']['bulk_form'] = [
      'title' => $this->t('Nbox metadata operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple threads.'),
      'field' => [
        'id' => 'nbox_metadata_bulk_form',
      ],
    ];

    return $data;
  }

}
