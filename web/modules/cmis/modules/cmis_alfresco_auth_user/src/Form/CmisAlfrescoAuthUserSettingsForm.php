<?php

declare(strict_types = 1);

namespace Drupal\cmis_alfresco_auth_user\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allow to enter the url of the alfresco endpoint to get a token.
 */
class CmisAlfrescoAuthUserSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cmis_alfresco_auth_user.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cmis_alfresco_auth_user_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cmis_alfresco_auth_user.settings');
    $form['alfresco_tickets_endpoint_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Alfresco tickets endpoint URL'),
      '#description' => $this->t('set Alfresco tickets URL eg: https://my.alfresco.com/alfresco/api/-default-/public/authentication/versions/1/tickets'),
      '#default_value' => $config->get('alfresco_tickets_endpoint_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('cmis_alfresco_auth_user.settings')
      ->set('alfresco_tickets_endpoint_url', $form_state->getValue('alfresco_tickets_endpoint_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
