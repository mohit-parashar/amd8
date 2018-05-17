<?php

/**
 * @file
 * Contains \Drupal\simplenews\Form\ConfirmAddForm.
 */

namespace Drupal\simplenews\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\simplenews\NewsletterInterface;

/**
 * Implements a add confirmation form for simplenews subscriptions.
 */
class ConfirmAddForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Confirm subscription');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Subscribe');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Thanks for Choosing us.');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplenews_confirm_add';
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
      '#markup' => '<p>' . t('Please confirm you would like to subscribe to the %newsletter Newsletter from America Media by clicking the Subscribe button below. You can change your subscription or unsubscribe in the future.', array('%user' => simplenews_mask_mail($mail), '%newsletter' => $newsletter->name)) . "<p>\n",
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
    \Drupal::service('simplenews.subscription_manager')->subscribe($form_state->getValue('mail'), $form_state->getValue('newsletter')->id(), FALSE, 'website');

/* Code for sending Data to mailchimp */
/*if($form_state->getValue('newsletter')->id() == "community"){
  $list = 'bfec9a8b56';
}else{
  $list = 'c150671f63';
}

$apikey = 'a3b309f9588758b28ea4b0d0109132fe-us6'; 
$auth = base64_encode( 'user:'.$apikey );
$email = $form_state->getValue('mail');
$data = array(
       'apikey'        => $apikey,
        'email_address' => $email,
        'status'        => 'subscribed');
$json_data = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://us6.api.mailchimp.com/3.0/lists/'.$list.'/members/');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.$auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);                                                                                                                  
$result = curl_exec($ch);



$apikey = 'a3b309f9588758b28ea4b0d0109132fe-us6'; 
$auth = base64_encode( 'user:'.$apikey );
$email = $form_state->getValue('mail');
$hash = md5(strtolower($email));
$data = array(
       'apikey'        => $apikey,
        'email_address' => $email,
        'status'        => 'subscribed');
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


/*
sscanf($result,"type:%c",$dev);
$test = 'Member Exists';
if ($dev ==== $test)
{
  echo"hello";
}
die(0);
*/

/*  api call end here for mailchimp */


    $config = \Drupal::config('simplenews.settings');
    if (!$path = $config->get('subscription.confirm_subscribe_page')) {
      $site_config = \Drupal::config('system.site');
      $path = $site_config->get('page.front');
      drupal_set_message(t('%user was added to the %newsletter mailing list.', array('%user' => $form_state->getValue('mail'), '%newsletter' => $form_state->getValue('newsletter')->name)));
    }

    $form_state->setRedirectUrl(Url::fromUri("internal:/$path"));
  }
}
