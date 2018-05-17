<?php
/**
 * @file
 * Contains Drupal\am_registration\Form\MeteredPaywallForm.
 */
namespace Drupal\am_metered_paywall\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Class SettingsForm.
 *
 * 
 */
class MeteredPaywallForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */

  public function defaultConfiguration() {
    $default_config = \Drupal::config('am_metered_paywall.settings');
    return array(
      'content_paywall_metered_limit' => $default_config->get('content_paywall_metered_limit'),
      'content_paywall_metered_limit_email' => $default_config->get('content_paywall_metered_limit_email'),
      'content_paywall_metered_limit_auth' => $default_config->get('content_paywall_metered_limit_auth'),
    );
  }

  /**
   * {@inheritdoc}
   */
  
  protected function getEditableConfigNames() {
    return [
      'am_metered_paywall.settings',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'am_metered_paywall_settings_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('am_metered_paywall.settings');	

    /*$types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
	  $types = $this->nodeTypeStorage->loadMultiple();*/

    $node_types = \Drupal\node\Entity\NodeType::loadMultiple();
    // If you need to display them in a drop down:

    $options = array();
    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }
	
	$form['metered_node_types'] = array(
      '#title' => $this->t('Select Content Type To Activate Paywall'),
      '#type' => 'checkboxes',      
      '#options' => $options,
      '#description' => $this->t('The number of pieces of metered content which can be viewed before all metered content is paywalled.'),
      '#default_value' => $config->get('metered_node_types'),
    );
	
	$form['content_paywall_metered_limit'] = array(
      '#title' => $this->t('Metered Limit For Anonymous User, Email not provided'),
      '#type' => 'textfield',
      '#size' => 3,
      '#field_suffix' => $this->t('pieces of content'),      
      '#description' => $this->t('The number of pieces of metered content which can be viewed before all metered content is paywalled.'),
      '#default_value' => $config->get('content_paywall_metered_limit'),
    );

  $form['content_paywall_metered_limit_email'] = array(
      '#title' => $this->t('Metered Limit For Anonymous User, Email provided but not confirmed'),
      '#type' => 'textfield',
      '#size' => 3,
      '#field_suffix' => $this->t('pieces of content'),      
      '#description' => $this->t('The number of pieces of metered content which can be viewed after user provides email.'),
      '#default_value' => $config->get('content_paywall_metered_limit_email'),
    );
	
	$form['content_paywall_metered_limit_auth'] = array(
      '#title' => $this->t('Metered Limit For Authenticated User'),
      '#type' => 'textfield',
      '#size' => 3,
      '#field_suffix' => $this->t('pieces of content'),      
      '#description' => $this->t('The number of pieces of metered content which can be viewed before populating donation and subscription popup.'),
      '#default_value' => $config->get('content_paywall_metered_limit_auth'),
    );
  $form['metered_authenticate_paywall_status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Paywall for authenticated users.'),
      '#default_value' => $config->get('metered_authenticate_paywall_status'),
    );
	$form['metered_anonymous_paywall_status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Paywall for anonymous users.'),
      '#default_value' => $config->get('metered_anonymous_paywall_status'),
    );
	// $form['content_paywall_cookie_expire'] = array(
	//   '#title' => $this->t('Cookie Expire'),
	//   '#type' => 'textfield',
	//   '#default_value' => $config->get('content_paywall_cookie_expire'),
	//   '#maxlength' => 4,
	//   '#size' => 3,
	//   '#description' => $this->t('Number of days.'),
	// );
    
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
    $this->config('am_metered_paywall.settings')
	
      ->set('content_paywall_metered_limit', $form_state->getValue('content_paywall_metered_limit'))
      ->set('content_paywall_metered_limit_email', $form_state->getValue('content_paywall_metered_limit_email'))
	  ->set('content_paywall_metered_limit_auth', $form_state->getValue('content_paywall_metered_limit_auth'))
      ->set('metered_node_types', $form_state->getValue('metered_node_types'))
      ->set('metered_authenticate_paywall_status', $form_state->getValue('metered_authenticate_paywall_status'))
      ->set('metered_anonymous_paywall_status', $form_state->getValue('metered_anonymous_paywall_status'))
	  // ->set('content_paywall_cookie_expire', $form_state->getValue('content_paywall_cookie_expire'))
	  
      ->save();
  }
}