<?php
/**
 * @file
 * Contains Drupal\am_sfs_integration\Form\SFS\SFSSettingsForm.
 */
namespace Drupal\am_sfs_integration\Form\SFS;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Class SFSSettingsForm.
 *
 * @package Drupal\xai\Form
 */
class SFSSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('am_sfs_integration.sfssettings');
    return array(
      'sfs_verify_customer_data_url' => $default_config->get('sfs_verify_customer_data_url'),
      'sfs_verify_customer_number_url' => $default_config->get('sfs_verify_customer_number_url'),
      'sfs_set_subscription_data_url' => $default_config->get('sfs_set_subscription_data_url'),
      'sfs_renew_subscription_url' => $default_config->get('sfs_renew_subscription_url'),
      'sfs_update_address_url' => $default_config->get('sfs_update_address_url'),
      'sfs_retrieve_cust_url' => $default_config->get('sfs_retrieve_cust_url'),
      'sfs_api_key_value' => $default_config->get('sfs_api_key_value'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'am_sfs_integration.sfssettings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sfssettings_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('am_sfs_integration.sfssettings');
    $form['endpoint'] = array(
      '#type' => 'details',
      '#title' => t('SFS endpoint settings'),
      '#description' => t('Respective endpoints for different API calls.'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    $form['key'] = array(
      '#type' => 'details',
      '#title' => t('SFS API Key'),
      '#description' => t('SFS API key.'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    $form['endpoint']['sfs_verify_customer_data_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Verify customer data endpoint'),
      '#default_value' => $config->get('sfs_verify_customer_data_url'),
      '#rows' => 15,
    );
    $form['endpoint']['sfs_verify_customer_number_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Verify customer number endpoint'),
      '#default_value' => $config->get('sfs_verify_customer_number_url'),
      '#rows' => 15,
    );
    $form['endpoint']['sfs_set_subscription_data_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Set subscription endpoint'),
      '#default_value' => $config->get('sfs_set_subscription_data_url'),
      '#rows' => 15,
    );
    $form['endpoint']['sfs_renew_subscription_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Renew subscription endpoint'),
      '#default_value' => $config->get('sfs_renew_subscription_url'),
      '#rows' => 15,
    );
    $form['endpoint']['sfs_update_address_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Update address endpoint'),
      '#default_value' => $config->get('sfs_update_address_url'),
      '#rows' => 15,
    );
    $form['endpoint']['sfs_retrieve_cust_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Retrieve CustID endpoint'),
      '#default_value' => $config->get('sfs_retrieve_cust_url'),
      '#rows' => 15,
    );
    $form['key']['sfs_api_key_value'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('APIKey Value'),
      '#default_value' => $config->get('sfs_api_key_value'),
      '#rows' => 15,
    );
    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('am_sfs_integration.sfssettings')
      ->set('sfs_verify_customer_data_url', $form_state->getValue('sfs_verify_customer_data_url'))
      ->set('sfs_verify_customer_number_url', $form_state->getValue('sfs_verify_customer_number_url'))
      ->set('sfs_set_subscription_data_url', $form_state->getValue('sfs_set_subscription_data_url'))
      ->set('sfs_renew_subscription_url', $form_state->getValue('sfs_renew_subscription_url'))
      ->set('sfs_update_address_url', $form_state->getValue('sfs_update_address_url'))
      ->set('sfs_retrieve_cust_url', $form_state->getValue('sfs_retrieve_cust_url'))
      ->set('sfs_api_key_value', $form_state->getValue('sfs_api_key_value'))
      ->save();
  }
}