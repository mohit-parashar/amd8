<?php
/**
 * @file
 * Contains Drupal\am_bsd_tools\Form\BSDAPI\BSDAPISettingsForm.
 */
namespace Drupal\am_bsd_tools\Form\BSDAPI;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Class BSDAPISettingsForm.
 *
 * @package Drupal\xai\Form
 */
class BSDAPILiveSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('am_bsd_tools.bsdlivesettings');
    return array(
      'get_constituents_by_id' => $default_config->get('get_constituents_by_id'),
      'get_constituents_by_email' => $default_config->get('get_constituents_by_email'),
      'merge_constituents_by_email' => $default_config->get('merge_constituents_by_email'),
      'set_constituent_data' => $default_config->get('set_constituent_data'),

      'list_constituent_groups' => $default_config->get('list_constituent_groups'),
      'remove_cons_ids_from_group' => $default_config->get('remove_cons_ids_from_group'),

      'get_deferred_results' => $default_config->get('get_deferred_results'),
      'loe' => $default_config->get('loe'),

      'email_register' => $default_config->get('email_register'),
      'email_delete' => $default_config->get('email_delete'),
      'send_triggered_email' => $default_config->get('send_triggered_email'),

      'Charge' => $default_config->get('Charge'),
      'get_by_token' => $default_config->get('get_by_token'),

      'bsd_api_key_value' => $default_config->get('bsd_api_key_value'),

      'donation_email' => $default_config->get('donation_email'),
      'one_time_login_link' => $default_config->get('one_time_login_link'),
      'email_address_update' => $default_config->get('email_address_update'),
      'newslatter_subscribe' => $default_config->get('newslatter_subscribe'),
      'contact_form_message' => $default_config->get('contact_form_message'),
      'donation_error' => $default_config->get('donation_error'),
      'donation_error_email' => $default_config->get('donation_error_email'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'am_bsd_tools.bsdlivesettings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bsdlivesettings_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('am_bsd_tools.bsdlivesettings');

    // BSD API Key
    $form['key'] = array(
      '#type' => 'details',
      '#title' => t('BSD API Key'),
      '#description' => t('BSD API key.'),
      '#open' => FALSE,
    );
    $form['key']['bsd_api_key_value'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('APIKey Value'),
      '#default_value' => $config->get('bsd_api_key_value'),
    );


    $form['endpoint'] = array(
      '#type' => 'details',
      '#title' => t('BSD endpoint settings'),
      '#description' => t('Respective endpoints for different API calls.'),
      '#open' => FALSE,
    );
    // BSD API constituents end point
    $form['endpoint']['constituents'] = array(
      '#type' => 'details',
      '#title' => t('BSD constituents endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['constituents']['get_constituents_by_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Get Consituent By ID'),
      '#default_value' => $config->get('get_constituents_by_id'),
    );
    $form['endpoint']['constituents']['get_constituents_by_email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Get Consituent By Email'),
      '#default_value' => $config->get('get_constituents_by_email'),
    );
    $form['endpoint']['constituents']['merge_constituents_by_email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('merge constituents by email '),
      '#default_value' => $config->get('merge_constituents_by_email'),
    );
    $form['endpoint']['constituents']['set_constituent_data'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Set constituent data'),
      '#default_value' => $config->get('set_constituent_data'),
    );

    // BSD API groups end point
    $form['endpoint']['groups'] = array(
      '#type' => 'details',
      '#title' => t('BSD groups endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['groups']['list_constituent_groups'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('List constituent groups'),
      '#default_value' => $config->get('list_constituent_groups'),
    );
    $form['endpoint']['groups']['remove_cons_ids_from_group'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Remove cons ids from group'),
      '#default_value' => $config->get('remove_cons_ids_from_group'),
    );

    // BSD API graph end point
    $form['endpoint']['graph'] = array(
      '#type' => 'details',
      '#title' => t('BSD graph endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['graph']['get_deferred_results'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Get deferred results'),
      '#default_value' => $config->get('get_deferred_results'),
    );
    $form['endpoint']['graph']['loe'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('LOE'),
      '#default_value' => $config->get('loe'),
    );

    // BSD API email end point
    $form['endpoint']['email'] = array(
      '#type' => 'details',
      '#title' => t('BSD email endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['email']['email_register'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email register'),
      '#default_value' => $config->get('email_register'),
    );
    $form['endpoint']['email']['email_delete'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email delete'),
      '#default_value' => $config->get('email_delete'),
    );
    $form['endpoint']['email']['send_triggered_email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Send triggered email'),
      '#default_value' => $config->get('send_triggered_email'),
    );

    // BSD API send triggered mailing ID end point
    $form['endpoint']['mailing'] = array(
      '#type' => 'details',
      '#title' => t('BSD API send Triggered mailing ID endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['mailing']['donation_email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donation'),
      '#default_value' => $config->get('donation_email'),
    );
    $form['endpoint']['mailing']['one_time_login_link'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('One time login link'),
      '#default_value' => $config->get('one_time_login_link'),
    );
    $form['endpoint']['mailing']['email_address_update'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email address update'),
      '#default_value' => $config->get('email_address_update'),
    );
    $form['endpoint']['mailing']['newslatter_subscribe'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Newslatter Subscribe'),
      '#default_value' => $config->get('newslatter_subscribe'),
    );
    $form['endpoint']['mailing']['contact_form_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Contact form message'),
      '#default_value' => $config->get('contact_form_message'),
    );
    $form['endpoint']['mailing']['donation_error'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donation error'),
      '#default_value' => $config->get('donation_error'),
    );

    // BSD API donation end point
    $form['endpoint']['donation'] = array(
      '#type' => 'details',
      '#title' => t('BSD donation endpoint settings'),
      '#open' => FALSE,
    );
    $form['endpoint']['donation']['Charge'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donation Charge'),
      '#default_value' => $config->get('Charge'),
    );
    $form['endpoint']['donation']['get_by_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donation Contribution'),
      '#default_value' => $config->get('get_by_token'),
    );
    $form['endpoint']['donation']['donation_error_email'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Donation error email'),
      '#default_value' => $config->get('donation_error_email'),
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
    $this->config('am_bsd_tools.bsdlivesettings')
      // Save api key
      ->set('bsd_api_key_value', $form_state->getValue('bsd_api_key_value'))
      // Save constituents end point
      ->set('get_constituents_by_id', $form_state->getValue('get_constituents_by_id'))
      ->set('get_constituents_by_email', $form_state->getValue('get_constituents_by_email'))
      ->set('merge_constituents_by_email', $form_state->getValue('merge_constituents_by_email'))
      ->set('set_constituent_data', $form_state->getValue('set_constituent_data'))
      // Save group end point
      ->set('list_constituent_groups', $form_state->getValue('list_constituent_groups'))
      ->set('remove_cons_ids_from_group', $form_state->getValue('remove_cons_ids_from_group'))
      // Save graph end point
      ->set('get_deferred_results', $form_state->getValue('get_deferred_results'))
      ->set('loe', $form_state->getValue('loe'))
      // Save email end point
      ->set('email_register', $form_state->getValue('email_register'))
      ->set('email_delete', $form_state->getValue('email_delete'))
      ->set('send_triggered_email', $form_state->getValue('send_triggered_email'))
      // Save donation end point
      ->set('Charge', $form_state->getValue('Charge'))
      ->set('get_by_token', $form_state->getValue('get_by_token'))
      // Save triggered mailing ID.
      ->set('donation_email', $form_state->getValue('donation_email'))
      ->set('one_time_login_link', $form_state->getValue('one_time_login_link'))
      ->set('email_address_update', $form_state->getValue('email_address_update'))
      ->set('newslatter_subscribe', $form_state->getValue('newslatter_subscribe'))
      ->set('contact_form_message', $form_state->getValue('contact_form_message'))
      ->set('donation_error', $form_state->getValue('donation_error'))
      ->set('donation_error_email', $form_state->getValue('donation_error_email'))
      
      ->save();
  }
}