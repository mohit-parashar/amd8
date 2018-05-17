<?php
/**
 * @file
 * Contains \Drupal\am_registration\Form\CreateAccountForm.
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
use Drupal\am_registration\Controller\SFS\VerifyUserSubscriptionNumber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\am_metered_paywall_statistics\Controller\StatisticsController;
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;

class CreateAccountForm extends FormBase {

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
    return 'create_account_form';
  }

   /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $form['#prefix'] = '<div id="loginModel">';
    // $form['#suffix'] = '</div>';
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
  $form['#attributes']['novalidate'] = 'novalidate';
  $form['reg_info'] = array(
    //'#type' => 'fieldset',
    '#prefix'=>"<div class='div-border'>",
    '#type' => container,
    //'#title' => t('Organization Information'), 
    '#attributes' => array('class' => array('clearfix','registration-info-wrapper')),    
    );
    $form['reg_info']['candidate_mail'] = array(
      '#type' => 'email',
      '#title' => t('Enter your email address'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('Email address'),'class' => array('form-control')),
      '#size' => 50,
      '#required' => TRUE,
    );
    $form['reg_info']['first_name'] = array(
      '#type' => 'textfield',
      '#title' => t('First Name:'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('First name'),'class' => array('form-control')),
      '#required' => TRUE,
    );
    $form['reg_info']['last_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Last Name:'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('Last name'),'class' => array('form-control')),
      '#required' => TRUE,
    );
    $form['reg_info']['print_subscription_number'] = array(
      '#type' => 'number',
      '#title' => t('Print subscription number:'),
      '#title_display' => 'invisible',
      '#attributes' => array('placeholder' => t('Print Subscription number'),'class' => array('form-control')),
      '#min' => 0,
      '#prefix' => "<div class='print-subscription-wrapper'>",
      '#field_suffix' => "<span class='tip' data-tip='tip-class'></span>
                    <div id='tip-class' class='tip-content hide'>
                    <span class='tip-image'></span>
                    </div></div></div>",
      '#states' => array(
        'visible' => array(
        ':input[name="is_print_subscriber"]' => array('checked' => TRUE),
        ),
        'invisible' => array(
        ':input[name="is_print_subscriber"]' => array('checked' => FALSE),
        ),
      ),
    );
    $form['reg_info']['is_print_subscriber'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('I have a print subscription'),
    );

    $form['reg_info']['actions']['#type'] = 'actions';
    $form['reg_info']['actions']['create_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#prefix' => '<div class="submit_button_login">',
      '#suffix' => '</div>',
    );
  
    $items = array();    
    $items['create_account'] = \Drupal::l(t('I want to use my password'), new Url('user.login', array(), array('attributes' => array('title' => t('Login with username and password.')))));    
    
    $form['user_links'] = array(     
      '#markup' => t('<div class="text-left loginLink"><a href="#">I already have an account</a></div>'),
      '#weight' => 100,      
    );
    return $form;
  }


  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      
      $name = trim($form_state->getValue('candidate_mail'));
      $first_name = trim($form_state->getValue('first_name'));
      $last_name = trim($form_state->getValue('last_name'));

      $print_subscription_number = trim($form_state->getValue('print_subscription_number'));
      $is_print_subscriber = $form_state->getValue('is_print_subscriber');
      
      // Validate Form fields
      if (empty($name) && empty($first_name) && empty($last_name) && empty($print_subscription_number) && $is_print_subscriber==1) {
        $form_state->setErrorByName('name', t('Email address, First name, Last name and Print subscription number cannot be empty.'));
      }

      if (empty($name) && empty($first_name) && empty($last_name)) {
        $form_state->setErrorByName('name', t('Email address, First name and Last name cannot be empty.'));
      }

      if (empty($first_name) && empty($last_name)) {
        $form_state->setErrorByName('name', t('First name and Last name cannot be empty.'));
      }

      if (empty($name)) {
        $form_state->setErrorByName('name', t('Email address cannot be empty.'));
      }
      if (empty($first_name)) {
        $form_state->setErrorByName('name', t('First name cannot be empty.'));
      }
      if (empty($last_name)) {
        $form_state->setErrorByName('name', t('Last name cannot be empty.'));
      }
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
      }

      // // If user checked have subscription no. checkbox but leaves subscription number field empty
      // if($is_print_subscriber== 1 && empty($print_subscription_number)){
      //   $form_state->setErrorByName('name', t('Please provide print subscription number.'));   
      // }

      // If account exists for provided email
      $users = $this->userStorage->loadByProperties(array('mail' => $name));
      $account = reset($users); 
      if ($account && $account->id()) {

        if (!$account->isActive() && !$account->get('field_is_email_validate')->value) {
          // Set the field value new value.
          $account->activate();
          $account->set("field_first_name", $first_name);
          $account->set("field_last_name", $last_name);
          // The crucial part! Save the $user object, else changes won't persist.
          $account->save();

          
          $bsdClient = new amBSDToolsController();
          $xml = '<?xml version="1.0" encoding="utf-8"?>
            <api>
              <cons>
                  <firstname>' . $first_name . '</firstname>
                  <lastname>' . $last_name . '</lastname>
                  <cons_field id="'.$bsdClient->bsd_env.'">
                    <value>Yes</value>
                  </cons_field>               
                  <cons_group id="15" />
                  <cons_email>
                    <email>'.$name.'</email>
                      <email_type>personal</email_type>
                      <is_subscribed>1</is_subscribed>
                      <is_primary>1</is_primary>
                    </cons_email>
              </cons>
            </api>';
            $bsdClient->setConstituentData($xml);
        }
        else {
          try{
              if (!$account->isActive()) {
                $CreateLoginLinkController = new CreateLoginLinkController();
                $link = $CreateLoginLinkController->createLoginLink($account,$flag='');
              }
          }catch (Exception $e) {
              drupal_set_message($e."Some error occured","error");
          }
        }
        // If user account is found
        // i.e email is already in use.

       $form_state->setErrorByName('name', $this->t('<div class="paywall-custom-error">%name is already registered — thank you! Please check your email or a link to log in automatically, or <div class="text-left loginLink"><a href="#" class="">click here</a></div> to log in with a password.</div>', array('%name' => $name)));

        //$form_state->setErrorByName('name', $this->t('%name is already taken.', array('%name' => $name)));
      }

      if ($is_print_subscriber == 1 && !empty($print_subscription_number)) {
        // Validate print subscription no. by API call
        // If user is print subscriber, update user and update print subscription field.
        // Provide user a subscriber role.
        try{
          
          $VerifyUsersubscriptionNumber = new VerifyUsersubscriptionNumber;
          $status = $VerifyUsersubscriptionNumber->verifyPSN($print_subscription_number);

        }catch (Exception $e) {
          drupal_set_message($e."Some error occured","error");
        }

        if($status != 1){
          $form_state->setErrorByName('print_subscription_number', $this->t('Subscription number is invalid.'));
        }
        else {
          if ($account && $account->id()) {
            $account->set("field_print_subscription_number", $print_subscription_number);
            $account->addRole('subscriber');
            $account->save();
            $bsdClient = new amBSDToolsController();

            $xml = '<?xml version="1.0" encoding="utf-8"?>
              <api>
                <cons>
                    <cons_field id="'.$bsdClient->bsd_env.'">
                      <value>Yes</value>
                    </cons_field>               
                    <cons_group id="15" />
                    <cons_email>
                      <email>'.$name.'</email>
                      </cons_email>
                      <cons_field id="'.$bsdClient->bsd_env_print_subscription.'">
                        <value>' . $print_subscription_number . '</value>
                      </cons_field>
                      <cons_field id="'.$bsdClient->bsd_env_is_subscriber.'">
                        <value>Yes</value>
                      </cons_field>
                </cons>
              </api>';
              $bsdClient->setConstituentData($xml);
              // $custData = $VerifyUsersubscriptionNumber->verifyPsnAndCustData($print_subscription_number);
              // if ($custData && empty($custData['Email'])) {
              //   $custData['Email'] = $user_mail;
              //   $VerifyUsersubscriptionNumber->updatePsnAddress($print_subscription_number, $custData);
              // }
          }
        }

      }
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
   
    $name = trim($form_state->getValue('candidate_mail'));
    $first_name = trim($form_state->getValue('first_name'));
    $last_name = trim($form_state->getValue('last_name'));
    $print_subscription_number = trim($form_state->getValue('print_subscription_number'));
    $is_print_subscriber = $form_state->getValue('is_print_subscriber');

    try{
         // Create a new user with provided email id and first name.
         $CreateUserController = new CreateUserController;
         if ($print_subscription_number != '') {
           $value = $CreateUserController->createUser($name,$first_name, $last_name, $print_subscription_number);
         }else{
           $value = $CreateUserController->createUser($name,$first_name, $last_name);
         }
         
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


      } catch (Exception $e) {
         drupal_set_message($e."Some error occured","error");
       }

    
   }
}