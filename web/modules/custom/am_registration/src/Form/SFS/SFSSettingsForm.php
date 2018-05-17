<?php
/**
 * @file
 * Contains Drupal\am_registration\Form\SFS\SFSSettingsForm.
 */
namespace Drupal\am_registration\Form\SFS;
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
    $default_config = \Drupal::config('am_registration.sfssettings');
    return array(
      'sfs_api_url' => $default_config->get('sfs_api_url'),
      'sfs_api_key_value' => $default_config->get('sfs_api_key_value'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'am_registration.sfssettings',
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
    $config = $this->config('am_registration.sfssettings');
    $form['sfs_api_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#default_value' => $config->get('sfs_api_url'),
      '#rows' => 15,
    );
    $form['sfs_api_key_value'] = array(
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
    $this->config('am_registration.sfssettings')
      ->set('sfs_api_url', $form_state->getValue('sfs_api_url'))
      ->set('sfs_api_key_value', $form_state->getValue('sfs_api_key_value'))
      ->save();
  }
}