<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_donation\Controller\CreateUserController;
use Drupal\am_donation\Controller\RenewSubscriptionController;
use Drupal\am_donation\Controller\CreateSubscriptionController;
use Symfony\Component\HttpFoundation\RedirectResponse;
// BSD controllor
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;

class SaveDonorInfoController extends ControllerBase {

  public function saveDonorInfo() {
    try{
        $values = array(
           'amount' => $_POST["amount"],
             'billing_address_1' => $_POST["billing_address_1"],
             'billing_address_2' => $_POST["billing_address_2"],
             'billing_city' => $_POST["billing_city"],
             'billing_country' => $_POST["billing_country"],
             'billing_postal_code' => $_POST["billing_postal_code"],
             'billing_province' => $_POST["billing_province"],
             'billing_state' => $_POST["billing_state"],
             'billing_state_province' => $_POST["billing_state_province"],
             'choose_designation' => $_POST["choose_designation"],
             'comments' => $_POST["comments"],
             'designate_gift' => $_POST["designate_gift"],
             'different_billing_address' => $_POST["different_billing_address"],
             'donor_email' => $_POST["donor_email"],
             'donor_phone' => $_POST["donor_phone"],
             'print_subscription_number' => $_POST["print_subscription_number"],
             'gift_anonymous' => $_POST["gift_anonymous"],
             'mailing_address_1' => $_POST["mailing_address_1"],
             'mailing_address_2' => $_POST["mailing_address_2"],
             'mailing_city' => $_POST["mailing_city"],
             'mailing_country' => $_POST["mailing_country"],
             'mailing_postal_code' => $_POST["mailing_postal_code"],
             'mailing_province' => $_POST["mailing_province"],
             'mailing_state' => $_POST["mailing_state"],
             'mailing_state_province' => $_POST["mailing_state_province"],
             'first_name'=> $_POST["first_name"],
             'last_name' => $_POST["last_name"],
             'other_designation' => $_POST["other_designation"],
             'payment_type' => $_POST["payment_type"],
             'phone' => $_POST["donor_phone"],
             'transaction_fee' => $_POST["transaction_fee"],
             'bsd_error' => $_POST["bsd_error"],
             'bsd_response' => $_POST["bsd_response"],
             'is_free_subscription_entitled' => $_POST["is_free_subscription_entitled"],
             'is_renew_subscription' => $_POST["is_renew_subscription"],
             'is_create_subscription' => $_POST["is_create_subscription"],
             'is_processed_via_cron' => $_POST["is_processed_via_cron"],
             'is_donation_success' => $_POST["is_donation_success"],
             'sfs_create_response' => $_POST["sfs_create_response"],
             'is_sfs_create_success' => $_POST["is_sfs_create_success"],
             'sfs_order_id' => $_POST['sfs_order_id'],
             'sfs_renew_response' => $_POST["sfs_renew_response"],
             'is_sfs_renew_success' => $_POST["is_sfs_renew_success"],
             'sfs_address_update_response' => $_POST["sfs_address_update_response"],
             'is_sfs_address_updated' => $_POST["is_sfs_address_updated"],
             'is_sfs_address_update_success' => $_POST["is_sfs_address_update_success"],
             'is_recurring_acknowledge' => $_POST["is_recurring_acknowledge"],

        );

        $webform_submission = '';

        if (isset($_POST["choose_designation"]) && $_POST["choose_designation"] == 'giving-tuesday') {
          $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
            'webform_id' => 'tuesday_donation',
          ]);
        }else if (isset($_POST["choose_designation"]) && $_POST["choose_designation"] == 'anniversary-challenge') {
          $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
            'webform_id' => 'anniversary_challenge',
          ]);
        } else {
          $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
            'webform_id' => 'donation',
          ]);
        }

        $webform_submission->setData($values);

        $webform_submission->save();
        echo $webform_submission->id();
        die();
      }catch (Exception $e) {
           drupal_set_message($e."Some error occured in SaveDonorInfoController","error");
         }
    }


  public function update_bsd_error(){

    $bsd_error = $_POST["bsd_error"];
    $sid = $_POST["sid"];
    global $base_url;

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $bsd_error,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'bsd_error');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $bsd_error,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'bsd_response');
    $query->execute();


    // Log BSD donation reponse in db.
    $requests = \Drupal::database()->select('webform_submission_data', 'w')
    ->fields('w', ['name', 'value'])
    //->condition('w.webform_id', 'donation', '=')
    ->condition('w.sid', $sid)
    ->execute()->fetchAll();
    $array = array();

    foreach ($requests as $key => $value) {
      $array[$value->name] = $value->value; 
    }

    $response_values = array(
      'request' => json_encode($array),
      'response' => '',
      'error' => $bsd_error,
    );

    $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
      'webform_id' => 'donation_log',
    ]);

    $webform_submission->setData($response_values);
    $webform_submission->save();
    // End of Log BSD donation reponse in db.



    $this->is_donation_success($sid,'0');

    echo "Updated successfuly";

  // Send email to site admin on un successful response.
  $message = json_decode($bsd_error);
    if ($message['status'] && $message['status'] == 0) {

      // Get API details
      $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
      // Check pantheon environment is live
      if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
        if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
          $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
        }
      }
      $mail_id = $config->get('donation_error');
      $user_mail = $config->get('donation_error_email');

      $error_msg = $base_url . '/admin/structure/webform/manage/donation/submission/' . $sid;

      $bsdClient = new amBSDToolsController();

      $xml .= '<?xml version="1.0" encoding="utf-8"?>
                <api>
                  <cons>
                      <cons_email>
                          <email>'.$user_mail.'</email>
                          <email_type>personal</email_type>
                          <is_subscribed>1</is_subscribed>
                          <is_primary>1</is_primary>
                      </cons_email>
                      <cons_field id="1">
                          <value>'.$error_msg.'</value>
                      </cons_field>
                  </cons>
                </api>';
      //$bsdClient->setConstituentData($xml);
      //$value = $bsdClient->amBSDToolsSendTriggeredMail($user_mail, $mail_id);

      $message='<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
  <tbody>
    <tr><td height="45">&nbsp;</td></tr>
    <tr align="center">
      <td><img alt="America Media" class="logo" src="https://s.bsd.net/diasparktest/main/page/-/template-images/logo.png" width="185" /></td>
    </tr>
  </tbody>
</table>
<table align="center" border="0" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:25px;" width="600">
  <tbody>
    <tr><td colspan="3" height="61">&nbsp;</td></tr>
    <tr><td class="mobilehidden" width="45">&nbsp;</td>
      <td>
        <table align="center" border="0" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:25px;" width="100%">
          <tbody>
            <tr><td>Hello Admin,</td></tr>
            <tr align="center"><td><p align="left">The donation was not proceed successfully. Please find below more information about error response in donation.</p><p align="left"><a href="'.$error_msg.'" target="_blank">'.$error_msg.'</a></p></td>
            </tr><tr align="center">
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>
                Enjoy your visit!</td>
            </tr>
            <tr>
              <td>
                &ndash;The America Team</td>
            </tr>
          </tbody>
        </table>
      </td>
      <td class="mobilehidden" width="45">
        &nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" height="65">
        &nbsp;</td>
    </tr>
  </tbody>
</table>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
  <tbody>
    <tr>
      <td bgcolor="#d8d2cc" colspan="3" height="1">
        &nbsp;</td>
    </tr>
    <tr>
      <td colspan="3" height="43">
        &nbsp;</td>
    </tr>
    <tr>
      <td class="mobilehidden" width="46">
        &nbsp;</td>
      <td>
        <table align="center" border="0" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif;font-size:12px; line-height:18px; color:#8d8d8d;" width="100%">
          <tbody>
            <tr>
              <td>
                Why am I receiving this message? You recently visited <a href="http://americamagazine.org/" style="color:#5433aa;" target="_blank">America Media.</a> and requested a log-in link to comment or access one of our other member-only resources. If you didn&rsquo;t make this request and have received this message by mistake, please disregard.</td>
            </tr>
            <tr>
              <td height="25">
                &nbsp;</td>
            </tr>
            <tr>
              <td>
                <b>&copy; 2017 America Media | All Rights Reserved | Facebook | Twitter | YouTube</b><br />
                106 West 56th St., New York, NY 10019-3803, USA<br />
                Visit <a href="http://americamagazine.org/" style="color:#5433aa;" target="_blank">America Media.</a> | Email Preferences</td>
            </tr>
          </tbody>
        </table>
          </td>
        <td class="mobilehidden" width="46">&nbsp;</td>
        </tr><tr><td colspan="3" height="135">&nbsp;</td></tr>
      </tbody>
    </table>
    <p>&nbsp;</p>';

      // Send email using SMTP
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'am_donation';
      $key = 'am_donation_bsd_error';
      $to = $user_mail;
      $params['message'] = $message;
      $params['title'] = 'Donation unknown error';
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;
      $params['headers'] = array(
        'content-type' => 'text/html',
        'MIME-Version' => '1.0',
        'reply-to' => 'webmaster@americamedia.org',
        'from' => 'webmaster@americamedia.org'
      );
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    }

  die();
  }

  public function object_to_array_recusive( $object, $assoc=TRUE, $empty='' )
  {

      $res_arr = array();

      if (!empty($object)) {

          $arrObj = is_object($object) ? get_object_vars($object) : $object;

          $i=0;
          foreach ($arrObj as $key => $val) {
              $akey = ($assoc !== FALSE) ? $key : $i;
              if (is_array($val) || is_object($val)) {
                  $res_arr[$akey] = (empty($val)) ? $empty : object_to_array_recusive($val);
              }
              else {
                  $res_arr[$akey] = (empty($val)) ? $empty : (string)$val;
              }

          $i++;
          }

      }

      return $res_arr;
  }
  public function update_bsd_response(){

    // Is new user and will get a subscription ?
    $is_first_time_subscriber = FALSE;
    // Initialise amount
    $amount = '';
    // Current date
    $current_date = date('Y-m-d');
    // Get BSD response
    $designation = '';
    $mailing_id = '';

    $bsd_response = base64_decode($_POST["bsd_response"]);
    $bsd_json_response = base64_decode($_POST["bsd_response"]);
    // Get webform submission id
    $sid = $_POST["sid"];
    // Get Payment type
    $payment_type = $_POST['payment_type'];
    // Get print subscription number, or customer Id
    $print_subscription_number = $_POST['print_subscription_number'];

    // Decode json response to array
    $bsd_response = ((array)json_decode($bsd_response));
    if ($bsd_response['reporting_data']->td->transaction_amt) {
      // Get transaction amount from BSD response
      $amount = $bsd_response['reporting_data']->td->transaction_amt;
    }

    // Update BSD response in drupal
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $bsd_json_response,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'bsd_response');
    $query->execute();

    // Log BSD donation reponse in db.
    $requests = \Drupal::database()->select('webform_submission_data', 'w')
    ->fields('w', ['name', 'value'])
    //->condition('w.webform_id', 'donation', '=')
    ->condition('w.sid', $sid)
    ->execute()->fetchAll();
    $array = array();

    foreach ($requests as $key => $value) {
      if ($value->name == 'choose_designation') {
        $designation = $value->value;
      }      
      $array[$value->name] = $value->value; 
    }

    $response_values = array(
      'request' => json_encode($array),
      'response' => $bsd_json_response,
      'error' => '',
    );

    $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
      'webform_id' => 'donation_log',
    ]);

    $webform_submission->setData($response_values);
    $webform_submission->save();
    // End of Log BSD donation reponse in db.

    // If Donation is success, register user in drupal and set various subscription flags.
    if ($bsd_response['status'] == 'success' && $amount) {

      $this->is_donation_success($sid,'1');

      $first_name = $bsd_response['reporting_data']->td->firstname;
      $last_name = $bsd_response['reporting_data']->td->lastname;
      $donor_email = $bsd_response['reporting_data']->td->email;

      // Try to load user by email.
      $users = user_load_by_mail($donor_email);

      $account = reset($users);

      if (!empty($account)) {
         // Provide donor role.
        $user = \Drupal\user\Entity\User::load($account['uid']['x-default']);
        $user->addRole('donor');
        $user->save();
       }else {
         try{
            //Create a new user with provided email id.
           $CreateUserController = new CreateUserController;
           $value = $CreateUserController->createUser($donor_email,$first_name,$last_name,1);
           $is_first_time_subscriber = TRUE;
         }catch (Exception $e) {
           drupal_set_message($e."Some create user error occured","error");
         }
       }

       // Set various SFS subscription flags for this donation.
          if ($payment_type == 'one-time') {

            if ($amount >= 200) {
              if ($print_subscription_number != '') {
                $this->set_is_renew_subscription($sid);
                $this->not_applicable_for_sfs_create_subscription($sid);
                // Renew subscription
                $this->provide_subscription($sid,0,1);
              } else {
                $this->set_is_create_subscription($sid);
                $this->not_applicable_for_sfs_renew_subscription($sid);
                // Create subscription
                $this->provide_subscription($sid,1,0);
              }
              $this->set_is_free_subscription($sid);
              // Not initialising, as we are providing subscriptions instantly.
              // $this->initialise_is_processed_via_cron($sid);
            }else{
              $this->not_applicable_for_sfs_subscription($sid);
            }
          } else {
            // Monthly Payment
            if (($amount * 12) >= 200) {
              if ($print_subscription_number != '') {
                $this->set_is_renew_subscription($sid);
                $this->not_applicable_for_sfs_create_subscription($sid);
                // Renew subscription
                $this->provide_subscription($sid,0,1);
              } else {
                $this->set_is_create_subscription($sid);
                $this->not_applicable_for_sfs_renew_subscription($sid);
                // Create subscription
                $this->provide_subscription($sid,1,0);
              }
                $this->set_is_free_subscription($sid);
                // $this->initialise_is_processed_via_cron($sid);
            }else{
              $this->not_applicable_for_sfs_subscription($sid);
            }
          }

      drupal_set_message(t('Your transaction was successfull. A confirmation email has been sent to %email with the details.',
            array(
                    '%email' => $donor_email,
                )
            ));

      try{

          $first_name = htmlspecialchars($first_name, ENT_XML1, 'UTF-8');
          $last_name = htmlspecialchars($last_name, ENT_XML1, 'UTF-8');

          // Send message from BSD system.
          $bsdClient = new amBSDToolsController();
          $xml = '<?xml version="1.0" encoding="utf-8"?>
                  <api>
                    <cons>';
          // Get user info, Firstname/Lastname
          $user_info = '';
          $user_info = $bsdClient->getConstituentsInfoByEmail($donor_email);
          // If cons is new then subscribe it to default group
          if ($user_info == 0 || $user_info == '0') {
            $xml .= '<cons_group id="15" />
            <firstname>'.$first_name.'</firstname>
            <lastname>'.$last_name.'</lastname>';
          } else {
            if ($user_info['fn'] == '') {
              $xml .= '<firstname>'.$first_name.'</firstname>';
            }
            if ($user_info['ln'] == '') {
              $xml .= '<lastname>'.$last_name.'</lastname>';
            }
          }
          if ($is_first_time_subscriber) {
            $xml .= '<cons_field id="9">
                            <value>'.$current_date.'</value>
                        </cons_field>';
          }
          if ($amount >= 200) {
            $xml .= '<cons_field id="7">
                            <value>Yes</value>
                        </cons_field>
                        <cons_field id="8">
                            <value>'.$current_date.'</value>
                        </cons_field>';
          }

                        $xml .= '<is_banned>0</is_banned>
                        <has_account>1</has_account>';

                    // Check pantheon environment is live
                    if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
                        if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
                            $xml .= '<cons_field id="3">
                                <value>'.$first_name.'</value>
                            </cons_field>
                            <cons_field id="4">
                                <value>'.$last_name.'</value>
                            </cons_field>
                            <cons_field id="5">
                                <value>'.$amount.'</value>
                            </cons_field>';
                          } else {
                            $xml .= '<cons_field id="4">
                                <value>'.$first_name.'</value>
                            </cons_field>
                            <cons_field id="5">
                                <value>'.$last_name.'</value>
                            </cons_field>
                            <cons_field id="3">
                                <value>'.$amount.'</value>
                            </cons_field>';

                          }
                      }
                        $xml .= '<cons_field id="6">
                            <value>Yes</value>
                        </cons_field>
                        <cons_email>
                            <email>'.$donor_email.'</email>
                            <email_type>personal</email_type>
                            <is_subscribed>1</is_subscribed>
                            <is_primary>1</is_primary>
                        </cons_email>
                    </cons>
                  </api>';

          // Get Mailing ID.
          $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
          // Check pantheon environment is live
          if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
            if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
              $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
            }
          }

          $mailing_id = $config->get('donation_email');

          if ($designation == 'giving-tuesday') {
            $mailing_id = 'VFA';
          }
          if ($designation == 'anniversary-challenge') {
            $mailing_id = 'VF0';
          }
          
          if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
            if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
              $mailing_id = 'V1UD';
              if ($designation == 'anniversary-challenge') {
                $mailing_id = 'VlIE';
              }
            }
          }

          $bsdClient->setConstituentData($xml);
          sleep(2);
          $bsdClient->amBSDToolsSendTriggeredMail($donor_email,$mailing_id);

      } catch (Exception $e) {
        drupal_set_message($e."Some error occured","error");
      }
          return new RedirectResponse('/thank-you-supporting');

    } else {

      $this->is_donation_success($sid,'0');
      $this->not_applicable_for_sfs_subscription($sid);
      $this->not_applicable_for_recurring_acknowledge($sid);

      drupal_set_message(t('%fail_message',
            array(
                    '%fail_message' => 'Your donation was not processed.',
                )
            ));

        if ($designation == 'giving-tuesday') {
          return new RedirectResponse('/giving-tuesday');
        }

        if ($designation == 'anniversary-challenge') {
          return new RedirectResponse('/anniversary-challenge');
        }

        return new RedirectResponse('/donation');
    }

  }

  // Eligible for cron
  // public function initialise_is_processed_via_cron($sid){

  //   $query = \Drupal::database()->update('webform_submission_data');
  //   $query->fields([
  //     'value' => 0,
  //   ]);
  //   $query->condition('sid', $sid);
  //   $query->condition('name', 'is_processed_via_cron');
  //   $query->execute();

  //   return true;
  // }

  // Is recurring acknowledge
  public function not_applicable_for_recurring_acknowledge($sid){

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_recurring_acknowledge');
    $query->execute();

    return true;
  }

  // SFS Subscription status

  public function set_is_free_subscription($sid){

  // $is_free_subscription = $_POST["is_free_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 1,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_free_subscription_entitled');
    $query->execute();

    return true;
  }

  public function set_is_create_subscription($sid){

  // $is_create_subscription = $_POST["is_create_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 1,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_create_subscription');
    $query->execute();

    return true;
  }

  public function set_is_renew_subscription($sid){

  // $is_renew_subscription = $_POST["is_renew_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 1,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_renew_subscription');
    $query->execute();

    return true;
  }

  public function is_donation_success($sid, $status){

  // $is_renew_subscription = $_POST["is_renew_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $status,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_donation_success');
    $query->execute();

    return true;
  }

  public function not_applicable_for_sfs_subscription($sid){

  // $is_renew_subscription = $_POST["is_renew_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_processed_via_cron');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_free_subscription_entitled');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_create_success');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_renew_success');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_address_update_success');
    $query->execute();

     $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_create_subscription');
    $query->execute();

     $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_renew_subscription');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_address_updated');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_address_update_response');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_create_response');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_order_id');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_renew_response');
    $query->execute();

    return true;
  }


  public function not_applicable_for_sfs_renew_subscription($sid){

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_renew_success');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_address_update_success');
    $query->execute();

     $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_renew_subscription');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_address_updated');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_address_update_response');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_renew_response');
    $query->execute();

    return true;
  }

  public function not_applicable_for_sfs_create_subscription($sid){

  // $is_renew_subscription = $_POST["is_renew_subscription"];
  // $sid = $_POST["sid"];

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_create_success');
    $query->execute();

     $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_create_subscription');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_create_response');
    $query->execute();

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => 'NA',
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_order_id');
    $query->execute();

    return true;
  }


  public function provide_subscription($sid,$create,$renew){

    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://")."/subscription-log";
    $file = \Drupal::service('file_system')->realpath(file_default_scheme() . "://")."/subscription-log/subscription-log.txt";
    $fp = fopen($file,"a+");

    try{

      $sids = array($sid);
      fwrite($fp, "======================================".PHP_EOL);
      fwrite($fp, "Subscription STARTS for Sid-> ".$sid.PHP_EOL);
      fwrite($fp, "Processed Sid-> ".$sid.PHP_EOL);
      fwrite($fp, "Processed Date-Time-> ".date("Y/m/d h:i:sa").PHP_EOL);
      if ($create == 1) {
        $CreateSubscriptionController = new CreateSubscriptionController;
        $response = $CreateSubscriptionController->create_subscription($sids);

        fwrite($fp, "Create subscription Processed for Sid-> ".$sid.PHP_EOL);
      }

      if ($renew == 1) {
        $RenewSubscriptionController = new RenewSubscriptionController;
        $response = $RenewSubscriptionController->renew_subscription($sids);

        fwrite($fp, "Renew subscription Processed for Sid-> ".$sid.PHP_EOL);
      }
      fwrite($fp, "Subscription ENDS for Sid-> ".$sid.PHP_EOL);
      fwrite($fp, "======================================".PHP_EOL);
    }catch (Exception $e) {
        fwrite($fp, $e."Some error occured".PHP_EOL);
             drupal_set_message($e."Some error occured","error");
           }


   fclose($fp);

   return true;
  }

}
