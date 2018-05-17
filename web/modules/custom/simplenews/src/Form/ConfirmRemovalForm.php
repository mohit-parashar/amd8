<?php

/**
 * @file
 * Contains \Drupal\simplenews\Form\ConfirmRemovalForm.
 */

namespace Drupal\simplenews\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\simplenews\NewsletterInterface;

/**
 * Implements a removal confirmation form for simplenews subscriptions.
 */
class ConfirmRemovalForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Confirm remove subscription');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Unsubscribe');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('This action will unsubscribe you from the newsletter mailing list.');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplenews_confirm_removal';
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('simplenews.newsletter_subscriptions');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mail = '', NewsletterInterface $newsletter = NULL) {
    $form = parent::buildForm($form, $form_state);
    $form['question'] = array(
      '#markup' => '<p>' . t('Are you sure you want to remove %user from the %newsletter mailing list?', array('%user' => simplenews_mask_mail($mail), '%newsletter' => $newsletter->name)) . "<p>\n",
    );
    $form['mail'] = array(
      '#type' => 'value',
      '#value' => $mail,
    );
    $form['newsletter'] = array(
      '#type' => 'value',
      '#value' => $newsletter,
    );
    return $form;
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
    \Drupal::service('simplenews.subscription_manager')->unsubscribe($form_state->getValue('mail'), $form_state->getValue('newsletter')->id(), FALSE, 'website');

/* Code for sending Data to mailchimp */

/*if($form_state->getValue('newsletter')->id() == "community"){
  $list = 'bfec9a8b56';
}else{
  $list = 'c150671f63';
}

$apikey = 'a3b309f9588758b28ea4b0d0109132fe-us6'; 
$auth = base64_encode( 'user:'.$apikey );
$email = $form_state->getValue('mail');
$hash = md5(strtolower($email));
$data = array(
       'apikey'        => $apikey,
        'email_address' => $email,
        'status'        => 'unsubscribed');
$json_data = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://us6.api.mailchimp.com/3.0/lists/".$list."/members/".$hash);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.$auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');                                                                                                                  
$result = curl_exec($ch);*/

/*  api call end here for mailchimp */

    $config = \Drupal::config('simplenews.settings');
    if (!$path = $config->get('subscription.confirm_unsubscribe_page')) {
      $site_config = \Drupal::config('system.site');
      $path = $site_config->get('page.front');
      drupal_set_message(t('%user was unsubscribed from the %newsletter mailing list.', array('%user' => $form_state->getValue('mail'), '%newsletter' => $form_state->getValue('newsletter')->name)));
    }

    $form_state->setRedirectUrl(Url::fromUri("internal:/$path"));
  }
}
