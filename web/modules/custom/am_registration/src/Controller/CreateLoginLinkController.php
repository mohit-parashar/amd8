<?php

namespace Drupal\am_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_registration\Controller\InsertLinkController;
use Drupal\am_registration\Controller\DeleteLinkController;
use Drupal\am_registration\Controller\SendLinkController;
use Drupal\am_registration\Controller\LoginCountsController;
// BSD controllor
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;
// SFS controller
use Drupal\am_registration\Controller\SFS\VerifyUserSubscriptionNumber;

class CreateLoginLinkController extends ControllerBase {

  public $site_env;
  public function __construct() {

    if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
      if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
        $this->site_env = 47;
      }
      else {
        $this->site_env = 10;
      }
    }

  }

  public function createLoginLink($user,$flag) {

  	global $base_url;
        $first_name = $user->get('field_first_name')->value;
        $last_name = $user->get("field_last_name")->value;
  	    // Get subscription number if any.
        $print_subscription_number = $user->get("field_print_subscription_number")->value;

        // Delete any previous link
        $delete_result = new DeleteLinkController;
        $value = $delete_result->delete($user->id());

  	     // Prepare one time login link.
         $uid = $user->id();
         $user_mail = $user->getEmail();
         $six_digit_random_number = mt_rand(100000, 999999);
         $login_hash = md5($user->getEmail().time()); // encrypted email+timestamp
         $created = time();

         //One time Login Link
         $link = $base_url.'/user/amlogin/'.$uid.'/'.$six_digit_random_number.'/'.$login_hash;
         // Added by arvind kinja

        // Get Mailing ID.
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $mail_id = $config->get('one_time_login_link');

         if($flag == 'newsletter') {
            $mail_id = $config->get('newslatter_subscribe');
            $link = $base_url.'/user/newsletter/'.$uid.'/'.$six_digit_random_number.'/'.$login_hash;
         } elseif($flag == 'email_verify') {
            $mail_id = $config->get('email_address_update');
            $user_mail = $user->get('field_email_verify')->value;
            $link = $base_url.'/user/email_update/'.$uid.'/'.$six_digit_random_number.'/'.$login_hash;
         } else {
            $mail_id = $config->get('one_time_login_link');
         }
         // Insert the created link into db.
         $insert_result = new InsertLinkController;
         $result = $insert_result->insert($uid,$user_mail,$six_digit_random_number,$login_hash,$created);

        try{
              // Send/Mail link to user.
              //$send_result = new SendLinkController;
              //$value = $send_result->sendMail($user,$link,$user_mail);
              //Send mail from bsd
              $bsdClient = new amBSDToolsController();

              //One time generate counts
              $LoginCountsController = new LoginCountsController;
              $status = $LoginCountsController->exists($user_mail);
              if($status == FALSE){
                $result = $LoginCountsController->insert($user_mail,$uid);
              }else{
                $generate_count = $LoginCountsController->getGenerateCount($user_mail);
                $LoginCountsController->updateGenerateCount($user_mail,$generate_count);
              }
              $consID = $user->get('field_cons_id')->value;
              $cons_id = $bsdClient->getConstituentsByEmail($user_mail);

              // we have added this because by mistake if user has cons id say 24 and in bsd if that consid doesnot have that email address, then we are correcting that cons id in drupal and setting message in that new cons_id    

              if ($consID != 0 && $consID != '' && $consID != $cons_id && $cons_id != 0) {
                $user->set("field_cons_id", $cons_id);
                $u = $user->save();
                $consID = $cons_id;
              }

              $xml = '';
              // Get user info, Firstname/Lastname
              $user_info = '';
              $user_info = $bsdClient->getConstituentsInfoByEmail($user_mail);              

              if($consID != '') {
                if($flag=='newslatter') {
                  $xml = '<?xml version="1.0" encoding="utf-8"?>
                      <api>
                        <cons id="'.$consID.'">';
                        $xml .= '<cons_group id="15" />';
                        $xml .='<cons_field id="1">
                                <value>'.$link.'</value>
                            </cons_field>
                        </cons>
                      </api>';
                } else if($flag == 'email_verify') {
                  $xml = '<?xml version="1.0" encoding="utf-8"?>
                      <api>
                        <cons id="'.$consID.'">';
                            // If cons is new then subscribe it to default group
                            if ($user_info == 0 || $user_info == '0') {
                              $xml .= '<cons_group id="15" />';
                            }
                            $xml .= '<is_banned>0</is_banned>
                            <has_account>1</has_account>
                            <cons_field id="1">
                                <value>'.$link.'</value>
                            </cons_field>
                            <cons_email>
                                <email>'.$user_mail.'</email>
                                <email_type>work</email_type>
                                <is_subscribed>1</is_subscribed>
                                <is_primary>0</is_primary>
                            </cons_email>
                        </cons>
                      </api>';
              } else {
                $xml = '<?xml version="1.0" encoding="utf-8"?>
                      <api>
                        <cons id="'.$consID.'">';
                            // If cons is new then subscribe it to default group
                            if ($user_info == 0 || $user_info == '0') {
                              $xml .= '<cons_group id="15" />';
                            }
                            $xml .= '
                            <firstname>'. $first_name .'</firstname>
                            <lastname>'. $last_name .'</lastname>
                            <cons_field id="1">
                                <value>'.$link.'</value>
                            </cons_field>
                            <cons_field id="2">
                                <value>One-time Login details for '.$user_mail.' at America Magazine</value>
                            </cons_field>                        
                        </cons>
                      </api>';
              }
            } else {
              if($flag=='newslatter' || $flag == 'email_verify') {
                 $xml = '<?xml version="1.0" encoding="utf-8"?>
                      <api>
                        <cons>';
                            // If cons is new then subscribe it to default group
                            if ($user_info == 0 || $user_info == '0' || $flag=='newslatter') {
                              $xml .= '<cons_group id="15" />';
                            }
                            $xml .= '<is_banned>0</is_banned>
                            <has_account>1</has_account>
                            <cons_field id="1">
                                <value>'.$link.'</value>
                            </cons_field>
                            <cons_field id="'.$this->site_env.'">
                              <value>Yes</value>
                            </cons_field>
                            <cons_email>
                                <email>'.$user_mail.'</email>
                                <email_type>personal</email_type>
                                <is_subscribed>1</is_subscribed>
                                <is_primary>1</is_primary>
                            </cons_email>
                            
                        </cons>
                      </api>';
              } else {
                // New user regestration case.

                $pshtml = '';
                if (!empty($print_subscription_number)) {
                  $pshtml = '<cons_field id="'.$bsdClient->bsd_env_print_subscription.'">
                              <value>' . $print_subscription_number . '</value>
                            </cons_field>
                            <cons_field id="'.$bsdClient->bsd_env_is_subscriber.'">
                              <value>Yes</value>
                            </cons_field>';
                  // $sfs = new VerifyUserSubscriptionNumber();
                  // $custData = $sfs->verifyPsnAndCustData($print_subscription_number);
                  // if ($custData && empty($custData['Email'])) {
                  //   $custData['Email'] = $user_mail;
                  //   $sfs->updatePsnAddress($print_subscription_number, $custData);
                  // }
                }

                 $xml = '<?xml version="1.0" encoding="utf-8"?>
                      <api>
                        <cons>';
                            // If cons is new then subscribe it to default group
                            // if ($user_info == 0 || $user_info == '0') {
                            //   $xml .= '<cons_group id="15" />';
                            // }
                            $xml .= '
                            <firstname>' . $first_name . '</firstname>
                            <lastname>' . $last_name . '</lastname>
                            <is_banned>0</is_banned>
                            <has_account>1</has_account>
                            <cons_field id="1">
                                <value>'.$link.'</value>
                            </cons_field>
                            <cons_field id="2">
                                <value>One-time Login details for '.$user_mail.' at America Magazine</value>
                            </cons_field>                        
                            <cons_email>
                                <email>'.$user_mail.'</email>
                                <email_type>personal</email_type>
                                <is_subscribed>1</is_subscribed>
                                <is_primary>1</is_primary>
                            </cons_email>
                            <cons_field id="'. $this->site_env .'">
                              <value>No</value>
                            </cons_field>
                            '. $pshtml .'
                        </cons>
                      </api>';

              }
          }

          $bsdClient = new amBSDToolsController();
          $bsdClient->setConstituentData($xml);
          $value = $bsdClient->amBSDToolsSendTriggeredMail($user_mail,$mail_id);
    } catch (Exception $e) {
      drupal_set_message($e."Some error occured","error");
    }
    return $result;
  }

} 