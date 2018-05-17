<?php

/**
 * @file
 * Contains \Drupal\simplenews\Controller\ConfirmationController.
 */

namespace Drupal\simplenews\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for confirmation routes.
 */
class ConfirmationController extends ControllerBase {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a UserPasswordForm object.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(UserStorageInterface $user_storage) {
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('user'),
      $container->get('language_manager')
    );
  }

  /**
   * Menu callback: confirm a combined confirmation request.
   *
   * This function is called by clicking the confirm link in the confirmation
   * email. It handles both subscription addition and subscription removal.
   *
   * @see simplenews_confirm_add_form()
   * @see simplenews_confirm_removal_form()
   *
   * @param $snid
   *   The subscriber id.
   * @param $timestamp
   *   The timestamp of the request.
   * @param $hash
   *   The confirmation hash.
   */
  public function confirmCombined($snid, $timestamp, $hash, $immediate = FALSE) {
    $config = \Drupal::config('simplenews.settings');

    // Prevent search engines from indexing this page.
    $html_head = array(
      array(
        '#tag' => 'meta',
        '#attributes' => array(
          'name' => 'robots',
          'content' => 'noindex',
        ),
      ),
      'simplenews-noindex',
    );

    $subscriber = simplenews_subscriber_load($snid);

    // Redirect and display message if no changes are available.
    if ($subscriber && !$subscriber->getChanges()) {
      drupal_set_message(t('All changes to your subscriptions where already applied. No changes made.'));
      return $this->redirect('<front>');
    }

    if ($subscriber && $hash == simplenews_generate_hash($subscriber->getMail(), 'combined' . serialize($subscriber->getChanges()), $timestamp)) {
      // If the hash is valid but timestamp is too old, display form to request
      // a new hash.
      if ($timestamp < REQUEST_TIME - $config->get('hash_expiration')) {
        $context = array(
          'simplenews_subscriber' => $subscriber,
        );
        $build = \Drupal::formBuilder()->getForm('\Drupal\simplenews\Form\RequestHashForm', 'subscribe_combined', $context);
        $build['#attached']['html_head'][] = $html_head;
        return $build;
      }
      // When not called with immediate parameter the user will be directed to the
      // (un)subscribe confirmation page.
      if (!$immediate) {
        $build = \Drupal::formBuilder()->getForm('\Drupal\simplenews\Form\ConfirmMultiForm', $subscriber);
        $build['#attached']['html_head'][] = $html_head;
        return $build;
      }
      else {

        /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
        $subscription_manager = \Drupal::service('simplenews.subscription_manager');

        // Redirect and display message if no changes are available.
        foreach ($subscriber->getChanges() as $newsletter_id => $action) {
          if ($action == 'subscribe') {
            $subscription_manager->subscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
          }
          elseif ($action == 'unsubscribe') {
            $subscription_manager->unsubscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
          }
        }

        // Clear changes.
        $subscriber->setChanges(array());
        $subscriber->save();

        drupal_set_message(t('Subscription changes confirmed for %user.', array('%user' => $subscriber->getMail())));
        return $this->redirect('<front>');
      }
    }
    throw new NotFoundHttpException();
  }

  /**
   * Menu callback: confirm the user's (un)subscription request
   *
   * This function is called by clicking the confirm link in the confirmation
   * email or the unsubscribe link in the footer of the newsletter. It handles
   * both subscription addition and subscription removal.
   *
   * Calling URLs are:
   * newsletter/confirm/add
   * newsletter/confirm/add/$HASH
   * newsletter/confirm/remove
   * newsletter/confirm/remove/$HASH
   *
   * @see simplenews_confirm_add_form()
   * @see simplenews_confirm_removal_form()
   */

  /**
   * Menu callback: confirm the user's (un)subscription request
   *
   * This function is called by clicking the confirm link in the confirmation
   * email or the unsubscribe link in the footer of the newsletter. It handles
   * both subscription addition and subscription removal.
   *
   * @see simplenews_confirm_add_form()
   * @see simplenews_confirm_removal_form()
   *
   * @param $action
   *   Either add or remove.
   * @param $snid
   *   The subscriber id.
   * @param $newsletter_id
   *   The newsletter id.
   * @param $timestamp
   *   The timestamp of the request.
   * @param $hash
   *   The confirmation hash.
   */
  function confirmSubscription($action, $snid, $newsletter_id, $timestamp, $hash, $immediate = FALSE) {
    $config = \Drupal::config('simplenews.settings');

    // Prevent search engines from indexing this page.
    $html_head = array(
      array(
        '#tag' => 'meta',
        '#attributes' => array(
          'name' => 'robots',
          'content' => 'noindex',
        ),
      ),
      'simplenews-noindex',
    );

    $subscriber = simplenews_subscriber_load($snid);
    if ($subscriber && $hash == simplenews_generate_hash($subscriber->getMail(), $action, $timestamp)) {
      $newsletter = simplenews_newsletter_load($newsletter_id);

      // If the hash is valid but timestamp is too old, display form to request
      // a new hash.
      if ($timestamp < REQUEST_TIME - $config->get('hash_expiration')) {
        $context = array(
          'simplenews_subscriber' => $subscriber,
          'newsletter' => $newsletter,
        );
        $token = $action == 'add' ? 'subscribe' : 'unsubscribe';
        $build = \Drupal::formBuilder()->getForm('\Drupal\simplenews\Form\RequestHashForm', $token, $context);
        $build['#attached']['html_head'][] = $html_head;
        return $build;
      }
      // When called with additional arguments the user will be directed to the
      // (un)subscribe confirmation page. The additional arguments will be passed
      // on to the confirmation page.
      if (!$immediate) {
        // if ($action == 'remove') {
        //   $build = \Drupal::formBuilder()->getForm('\Drupal\simplenews\Form\ConfirmRemovalForm', $subscriber->getMail(), $newsletter);
        //   $build['#attached']['html_head'][] = $html_head;
        //   return $build;
        // }
        // elseif ($action == 'add') {
        //   $build = \Drupal::formBuilder()->getForm('\Drupal\simplenews\Form\ConfirmAddForm', $subscriber->getMail(), $newsletter);
        //   $build['#attached']['html_head'][] = $html_head;
        //   return $build;
        // }
        /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
        $subscription_manager = \Drupal::service('simplenews.subscription_manager');

        if ($action == 'remove') {
          $subscription_manager->unsubscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
          // if ($path = $config->get('subscription.confirm_unsubscribe_page')) {
          //   return $this->redirect(Url::fromUri("internal:/$path")->getRouteName());
          // }
          /* Code for sending Data to mailchimp */
/*if($newsletter_id == "community_page_updates"){
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
$email = $subscriber->getMail();
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

/*  api call end here for mailchimp */
          drupal_set_message(t('%user was unsubscribed from the %newsletter mailing list.', array('%user' => $subscriber->getMail(), '%newsletter' => $newsletter->name)));
          return $this->redirect('<front>');
        }
        elseif ($action == 'add') {
          //$subscription_manager->subscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
          /* Code for sending Data to mailchimp */
/*if($newsletter_id == "community_page_updates"){
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
$email = $subscriber->getMail();
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
          // if ($path = $config->get('subscription.confirm_subscribe_page')) {
          //   return $this->redirect(Url::fromUri("internal:/$path")->getRouteName());
          // }
          drupal_set_message(t('%user was subscribed to the %newsletter mailing list.', array('%user' => $subscriber->getMail(), '%newsletter' => $newsletter->name)));
          $mail = $subscriber->getMail();
          // Load the subscribed user.
          // $user = \Drupal\user\Entity\User::load(\Drupal::$mail);
          $user = $this->userStorage->loadByProperties(array('mail' => $mail));
          $account = reset($user);
          if ($account && $account->id()) {
            user_login_finalize($account);
          }else{
             // Create user if not registered
          $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
          $user = \Drupal\user\Entity\User::create();

          
           // Mandatory.
           $pass = explode('@',$mail);

           $user->setPassword('@a!m'.$pass[0].'!a@m');
           $user->enforceIsNew();
           $user->setEmail($mail);
           $user->setUsername($mail);

           // Optional.
           //$user->set('init', 'email');
           //$user->set('langcode', $language);
           $user->set('preferred_langcode', $language);
           //$user->set('preferred_admin_langcode', $language);
           //$user->set('setting_name', 'setting_value');
           //$user->addRole('rid');
           $user->activate();

           // Save user account.
           $result = $user->save();
           user_login_finalize($user);
          }

          return $this->redirect('<front>');
        }
      }
      else {

        // /** @var \Drupal\simplenews\Subscription\SubscriptionManagerInterface $subscription_manager */
        // $subscription_manager = \Drupal::service('simplenews.subscription_manager');

        // if ($action == 'remove') {
        //   $subscription_manager->unsubscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
        //   // if ($path = $config->get('subscription.confirm_unsubscribe_page')) {
        //   //   return $this->redirect(Url::fromUri("internal:/$path")->getRouteName());
        //   // }
        //   drupal_set_message(t('%user was unsubscribed from the %newsletter mailing list.', array('%user' => $subscriber->getMail(), '%newsletter' => $newsletter->name)));
        //   return $this->redirect('<front>');
        // }
        // elseif ($action == 'add') {
        //   $subscription_manager->subscribe($subscriber->getMail(), $newsletter_id, FALSE, 'website');
        //   // if ($path = $config->get('subscription.confirm_subscribe_page')) {
        //   //   return $this->redirect(Url::fromUri("internal:/$path")->getRouteName());
        //   // }
        //   drupal_set_message(t('%user was added to the %newsletter mailing list.', array('%user' => $subscriber->getMail(), '%newsletter' => $newsletter->name)));
        //   return $this->redirect('<front>');
        // }
      }
    }
    throw new NotFoundHttpException();
  }
}
