<?php

namespace Drupal\entity_visitors\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_media_links\Plugin\SocialMediaLinks\Platform\Drupal;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class EntityVisitiorsConfig.
 */
class EntityVisitiorsConfig extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'entity_visitors.entityvisitiorsconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'entity_visitiors_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('entity_visitors.entityvisitiorsconfig');
    $form['excluded_roles'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#description' => t("Excluding a role means that you won't count it's visits to the entities."),
      '#title' => $this->t('Excluded Roles'),
      '#options' => user_role_names(),
      '#default_value' => $config->get('excluded_roles') ?: [],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $this->config('entity_visitors.entityvisitiorsconfig')
      ->set('excluded_roles', $form_state->getValue('excluded_roles'))
      ->save();
  }

}
