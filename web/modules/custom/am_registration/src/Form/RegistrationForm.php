<?php
/**
 * @file
 * Contains \Drupal\am_registration\Form\RegistrationForm.
 */
namespace Drupal\am_registration\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element\Email;
use Drupal\user\UserStorageInterface;
use Drupal\am_registration\Controller\CreateUserController;
use Drupal\am_registration\Controller\CreateLoginLinkController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\am_metered_paywall_statistics\Controller\StatisticsController;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;

// BSD controllor
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;

class RegistrationForm extends FormBase {

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
  public function __construct(UserStorageInterface $user_storage, LanguageManagerInterface $language_manager) {
    $this->userStorage = $user_storage;
    $this->languageManager = $language_manager;
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'registration_form';
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id="loginModel">';
    $form['#suffix'] = '</div>';
    $form['#attributes']['novalidate'] = 'novalidate';
  /*
  $form['description'] = array(
    '#prefix'=>"<div class='whyContent'>",
    '#type' => 'item',
    '#attributes' => array('class' => array('registration-account-description')),
    '#description' => t('<a href="#whyAnswer" data-toggle="collapse" aria-expanded="false" aria-controls="whyAnswer" class="whyQue collapsed">Why am I being asked to log in?</a> <span id="whyAnswer" class="collapse" aria-expanded="false" style="height: 0px;">
    if you are trying to comment, you must log in or set up a new account. this helps us promote a safe and accountable online community, and allows us to update you when other commenters reply to your posts. <a href="/privacy-policy" class="readPolicies"> Read more about our policies</a> | <a href="#whyAnswer" data-toggle="collapse" aria-expanded="false" aria-controls="whyAnswer" class="closeMsg collapsed">Close this message</a></span>'),
    '#suffix'=> "</div>",
    );
   */
  $form['reg_info'] = array(
    //'#type' => 'fieldset',
    '#prefix'=>"<div class='div-border'>",
    '#type' => container,
    //'#title' => t('Organization Information'),
    '#attributes' => array('class' => array('clearfix','registration-info-wrapper')),
    );

    // Restore data.
/*    $social_data = @unserialize($_SESSION['social_login_social_data']);

    // Convenience variables.
    $data = $social_data['response']['result']['data'];
    $identity = $data['user']['identity'];
    $identity_id = $identity['id'];
    $provider_name = $identity['source']['name'];

    // Email.
    $user_email = '';
    $user_email_is_verified = FALSE;
    if (isset($identity['emails']) && is_array($identity['emails'])) {
      while (!$user_email_is_verified && (list (, $email) = each($identity['emails']))) {
        $user_email = $email['value'];
        $user_email_is_verified = (!empty($email['is_verified']));
      }
    }
    if($user_email != '') {
      $form['reg_info']['message'] = array(
        '#markup' => '<div class="login-message"><div class="alert alert-danger alert-dismissible" role="alert">
          <a href="" role="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></a><ul class="item-list--messages"><li class="item item--message">Your email address '.$user_email.' is already registered with an account. Please login to that account by sending a login link, using your password, or selecting the social network you created your account.
          </li></ul></div></div>',
      );
    }*/

    $form['reg_info']['candidate_mail'] = array(
      '#type' => 'email',
      '#title' => t('Enter your email address'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('Email address'),'class' => array('form-control')),
      '#size' => 50,
      '#required' => TRUE,
    );

    $form['reg_info']['form_ty'] = array(
      '#title' =>  t('Form Type'),
      '#default_value' => 0,
      '#type' => 'hidden',
    );

    $form['reg_info']['password'] = array(
      '#type' => 'password',
      '#title' => t('Password:'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('Password'),'class' => array('form-control')),
      '#required' => FALSE,
      '#states' => array(
        'visible' => array(
        ':input[name="has_password"]' => array('checked' => TRUE),
        ),
        'invisible' => array(
        ':input[name="has_password"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['reg_info']['has_password'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('I have a password'),
    );
    $form['reg_info']['actions']['#type'] = 'actions';
    $form['reg_info']['actions']['#prefix'] = '<div class="submit_button_login">';
    $form['reg_info']['actions']['#suffix'] = '</div>';
    $form['reg_info']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send me a login link'),
      '#button_type' => 'primary',
      '#states' => array(
        'visible' => array(
        ':input[name="has_password"]' => array('checked' => FALSE),
        ),
        'invisible' => array(
        ':input[name="has_password"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['reg_info']['actions']['submit1'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Login'),
      '#button_type' => 'primary',
      '#states' => array(
        'visible' => array(
        ':input[name="has_password"]' => array('checked' => TRUE),
        ),
        'invisible' => array(
        ':input[name="has_password"]' => array('checked' => FALSE),
        ),
      ),
    );

    $items = array();
    $items['create_account'] = \Drupal::l(t('I want to use my password'), new Url('user.login', array(), array('attributes' => array('title' => t('Login with username and password.')))));

    $form['user_links'] = array(
      '#markup' => t('<div class="text-left create-account-link"><a href="#">Create a new account</a></div>'),
      '#weight' => 100,
    );
    return $form;
  }


  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    $name = trim($form_state->getValue('candidate_mail'));
    $password = $form_state->getValue('password');
    $has_password = $form_state->getValue('has_password');

    // If user does not have password
    if($has_password!= 1){

      // Validate email address using BSD system.
      if (valid_email_address($name)) {
        $bsdClient = new amBSDToolsController();
        $status_code = $bsdClient->emailRegister($name);
        if($status_code == "error") {
          $form_state->setErrorByName('name', t('some error occurd.'));
        }
      }

      if (!valid_email_address($name)) {
        // If user provides invalid email format.
        $form_state->setErrorByName('name', t('The email address appears to be invalid.'));
      }else{
         $users = $this->userStorage->loadByProperties(array('mail' => $name));
         $account = reset($users);
        if ($account && $account->id()) {

          $form_state->set('am_send_mail', TRUE);
          // If user account is found & entering its email second time, than is should be confrimed user.
          if (!$account->isActive() && !$account->get('field_is_email_validate')->value) {
            // Set the field value new value.
            $account->activate();
            // The crucial part! Save the $user object, else changes won't persist.
            $account->save();
            $form_state->set('am_send_mail', FALSE);
          }
          \Drupal::logger('am_widget')->notice($form_state->getValue('form_ty'));
          if ($form_state->getValue('form_ty')) {
            $form_state->set('am_send_mail', FALSE);
          }

          if (!$account->isActive()) {
              // If user is blocked
              $form_state->setErrorByName('name', $this->t('%name is blocked or has not been activated yet.', array('%name' => $name)));
            }
         }
      }
    }else{
      // If user has password
      if (!valid_email_address($name)) {
        // If user provides invalid email format.
        $form_state->setErrorByName('name', t('Email address or password appears to be invalid.'));
      }else{
         $users = $this->userStorage->loadByProperties(array('mail' => $name));
         $account = reset($users);
         if ($account && $account->id()) {
          // If user account is found
            if (!$account->isActive()) {
              // If user is blocked
              $form_state->setErrorByName('name', $this->t('%name is blocked or has not been activated yet.', array('%name' => $name)));
            }else{
              // User authentication
              // Get username to validate account
              $user_name = $account->getUsername();
              // Inject user.auth service..
              // Validates user account for given password and username.
              $uid = \Drupal::service('user.auth')->authenticate($user_name, $password);
              if ($uid==FALSE) {
                // Wrong credentials
                // $form_state->setErrorByName('name', $this->t('%name or password in not valid.', array('%name' => $name)));
                $form_state->setErrorByName('name', t('Email address or password appears to be invalid.'));
              }
            }
         }else{
          // If user account is not found
          $form_state->setErrorByName('name', t('Email address or password appears to be invalid.'));
         }
      }
    }
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $name = trim($form_state->getValue('candidate_mail'));
    $password = $form_state->getValue('has_password');

    if ($password!='') {
      $users = $this->userStorage->loadByProperties(array('mail' => $name));
      $account = reset($users);

      user_login_finalize($account);
      drupal_set_message($this->t('<div class="AMloginMsg"><div class="titleKarlaBold">Welcome! You are now logged in.</div></div>'));
      return;
    }

    // Try to load by email.
    $users = $this->userStorage->loadByProperties(array('mail' => $name));
    if (empty($users)) {
      // No success, try to load by name.
      $users = $this->userStorage->loadByProperties(array('name' => $name));
    }

    $account = reset($users);
    if ($account && $account->id() && $account->isActive()) {

      if ($form_state->get('am_send_mail')) {
        try{
          $CreateLoginLinkController = new CreateLoginLinkController;
          $value = $CreateLoginLinkController->createLoginLink($account);

        }catch (Exception $e) {
          drupal_set_message($e."Some error occured","error");
        }
      }
     }else {
       try{
          //Create a new user with provided email id.
         $CreateUserController = new CreateUserController;
         $value = $CreateUserController->createUser($name);

         // Paywall statistics
      if(isset($_COOKIE['paywallcookie'])) {
         $node = \Drupal::routeMatch()->getParameter('node');
         if ($node) {
            // You can get nid and anything else you need from the node object.
            $current_nid = $node->id();

            // Get metered nodes
            $metered_nodes = explode(',', $_COOKIE['meteredValue']);
            $StatisticsController = new StatisticsController;
            $result = $StatisticsController->record_registration($metered_nodes,$current_nid);
         }
         setcookie('paywallcookie', '', 1,'/');
         }

       }catch (Exception $e) {
         drupal_set_message($e."Some error occured","error");
       }
     }
   }
}
