<?php

/**
 * @file
 * Contains \Drupal\simplenews\Form\SubscriptionsBlockForm.
 */

namespace Drupal\simplenews\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configure simplenews subscriptions of the logged user.
 */
class SubscriptionsBlockForm extends SubscriptionsFormBase {

  protected $uniqueId;

  /**
   * A message to use as description for the block.
   *
   * @var string
   */
  public $message;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (empty($this->uniqueId)) {
      throw new \Exception('Unique ID must be set with setUniqueId.');
    }
    return 'simplenews_subscriptions_block_' . $this->uniqueId;
  }

  /**
   * {@inheritdoc}
   */
  public function setUniqueId($id) {
    $this->uniqueId = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Hide subscription widget if only one newsletter available.
    if (count($this->getNewsletters()) == 1) {
      $this->getSubscriptionWidget($form_state)->setHidden();
    }

    $form = parent::form($form, $form_state);

    $form['message'] = array(
      '#type' => 'item',
      '#markup' => $this->message,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // If only one newsletter, show Subscribe/Unsubscribe instead of Update.
    $actions = parent::actions($form, $form_state);
    if ($this->getOnlyNewsletterId() != NULL) {
      $actions[static::SUBMIT_UPDATE]['#access'] = FALSE;
      $actions[static::SUBMIT_SUBSCRIBE]['#access'] = !$this->entity->isSubscribed($this->getOnlyNewsletterId());
      $actions[static::SUBMIT_UNSUBSCRIBE]['#access'] = $this->entity->isSubscribed($this->getOnlyNewsletterId());
    }
    return parent::actions($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSubmitMessage(FormStateInterface $form_state, $op, $confirm) {
    switch ($op) {
      case static::SUBMIT_UPDATE:
        return $this->t('The newsletter subscriptions for %mail have been updated.', array('%mail' => $form_state->getValue('mail')[0]['value']));

      case static::SUBMIT_SUBSCRIBE:
        if ($confirm) {
          //return $this->t('You will receive a confirmation e-mail shortly containing further instructions on how to complete your subscription.');
          return;
        }


          /* Code for sending Data to mailchimp */
/*$newsletter_id = $this->getOnlyNewsletterId();

if($newsletter_id == "community_page_updates"){
 $interest_id = '3d7cbcefd0'; // YOUR INTEREST/GROUP ID
}
elseif($newsletter_id == "weekly_magazine"){
  $interest_id = 'ffe90c1f2d'; // YOUR INTEREST/GROUP ID
}
elseif($newsletter_id == "catholic_book_club"){
  $interest_id = '01922e4a1b'; // YOUR INTEREST/GROUP ID
}
else{
  $interest_id = 'f78aee90b8'; // YOUR INTEREST/GROUP ID
}


$api_key = 'a3b309f9588758b28ea4b0d0109132fe-us6'; // YOUR API KEY
$list_id = '0fe8ed70be'; // YOUR LIST ID
$auth = base64_encode( 'user:'.$api_key );
$email = $form_state->getValue('mail')[0]['value'];
$memberHash = md5(strtolower($email));   
$data = array(
    'apikey'        => $api_key,
    'email_address' => $email,
    'status'        => 'subscribed',
    'interests'        => array(
            $interest_id => true
            ),
    );
$json_data = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://us6.api.mailchimp.com/3.0/lists/'.$list_id.'/members/'.$memberHash);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '.$auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data); 
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
        return $this->t('You have been subscribed.');

      case static::SUBMIT_UNSUBSCRIBE:
        if ($confirm) {
          return $this->t('You will receive a confirmation e-mail shortly containing further instructions on how to cancel your subscription.');
        }
   /* Code for sending Data to mailchimp */

/*$newsletter_id = $this->getOnlyNewsletterId();

if($newsletter_id == "community_page_updates"){
 $interest_id = '3d7cbcefd0'; // YOUR INTEREST/GROUP ID
}
elseif($newsletter_id == "weekly_magazine"){
  $interest_id = 'ffe90c1f2d'; // YOUR INTEREST/GROUP ID
}
elseif($newsletter_id == "catholic_book_club"){
  $interest_id = '01922e4a1b'; // YOUR INTEREST/GROUP ID
}
else{
  $interest_id = 'f78aee90b8'; // YOUR INTEREST/GROUP ID
}


$api_key = 'a3b309f9588758b28ea4b0d0109132fe-us6'; // YOUR API KEY
$list_id = '0fe8ed70be'; // YOUR LIST ID
$auth = base64_encode( 'user:'.$api_key );
$email = $form_state->getValue('mail')[0]['value'];
$memberHash = md5(strtolower($email));   
$data = array(
    'apikey'        => $api_key,
    'email_address' => $email,
    'status'        => 'subscribed',
    'interests'        => array(
            $interest_id => false
            ),
    );
$json_data = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://us6.api.mailchimp.com/3.0/lists/'.$list_id.'/members/'.$memberHash);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '.$auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data); 
$result = curl_exec($ch);*/
   /* Code for sending Data to mailchimp ends here */
        return $this->t('You have been unsubscribed.');
    }
  }

}
