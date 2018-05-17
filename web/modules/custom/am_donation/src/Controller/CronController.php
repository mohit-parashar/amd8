<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_donation\Controller\RenewSubscriptionController;
use Drupal\am_donation\Controller\CreateSubscriptionController;

class CronController extends ControllerBase {

 /**
  * SFS create/renew subscription cron task. Fetch all records i.e 'sid' from 'webform_submission_data' table where 'is_free_subscripiton_entitled' = 1 and 'is_processed_via_cron' = 0
  * For the above records check if it is 'is_create_subscription' or 'is_renew_subscription'
  * 
  * Case: 'is_create_subscription' = 1
  * Create a new subcription by calling create subscription call.
  * Capture respone and save in 'sfs_create_response'
  * 
  * Case: 'is_renew_subscription' = 1
  * Renew subscription by calling renew subscription call.
  * Capture respone and save in 'sfs_renew_response'
  * 
  * If create or renew subscription call gets success:
  * Case RENEW:
  * Call update address API
  * set 'is_processed_via_cron' = 1
  * Provide subscriber role and donor role if user is logged in.
  * 
  * Case CREATE:
  * set 'is_processed_via_cron' = 1
  * Provide subscriber role and donor role if user is logged in.
  */

 public function donation_subscription_cron(){

  die("Turned off");


  echo $_SERVER['REMOTE_ADDR'];

  if($_SERVER['REMOTE_ADDR'] == '125.22.9.133'){
  try{
  
    // Get list of donations unprocessed via cron for subscription.
      $query = \Drupal::database()->select('webform_submission_data', 'wsd');
      $query->fields('wsd', ['sid']);
      $query->condition('wsd.value', '0','=');
      $query->condition('wsd.name', 'is_processed_via_cron', '=');
      $result = $query->execute()->fetchAllAssoc('sid');

      // echo "<pre>";
      // print_r($unprocessed_sids);
      $unprocessed_sids = array_keys($result);
      // foreach($unprocessed_sids as $k=>$v){
      //   $unprocessed_sids[] = $v;
      // }
      // echo "Not processed ids";
      // print_r($unprocessed_sids);
  if (!empty($unprocessed_sids)) {
    
    // Get list of unprocessed donations entitle for free subscription.
      $query = \Drupal::database()->select('webform_submission_data', 'wsd');
      $query->fields('wsd', ['sid']);
      $query->condition('wsd.sid', $unprocessed_sids,'IN');
      $query->condition('wsd.value', '1','=');
      $query->condition('wsd.name', 'is_free_subscription_entitled', '=');
      $result = $query->execute()->fetchAllAssoc('sid');
      $free_sub_entitled_sids = array_keys($result);
      echo "Free subscription entitled ids";
      print_r($free_sub_entitled_sids);
      
      if (!empty($free_sub_entitled_sids)) {
    // Get list of donors who are entitled for free 'RENEW' subscription and are not processed via cron.
      $query = \Drupal::database()->select('webform_submission_data', 'wsd');
      $query->fields('wsd', ['sid']);
      $query->condition('wsd.sid', $free_sub_entitled_sids,'IN');
      $query->condition('wsd.value', '1','=');
      $query->condition('wsd.name', 'is_create_subscription', '=');
      $result = $query->execute()->fetchAllAssoc('sid');
      $free_sub_entitled_create_sids = array_keys($result);
      echo "Create sub ids";
      print_r($free_sub_entitled_create_sids);

      if (!empty($free_sub_entitled_create_sids)) {
        $CreateSubscriptionController = new CreateSubscriptionController;
        $response = $CreateSubscriptionController->create_subscription($free_sub_entitled_create_sids);
      }


    // Get list of donors who are entitled for free 'RENEW' subscription and are not processed via cron.
      $query = \Drupal::database()->select('webform_submission_data', 'wsd');
      $query->fields('wsd', ['sid']);
      $query->condition('wsd.sid', $free_sub_entitled_sids,'IN');
      $query->condition('wsd.value', '1','=');
      $query->condition('wsd.name', 'is_renew_subscription', '=');
      $result = $query->execute()->fetchAllAssoc('sid');
      $free_sub_entitled_renew_sids = array_keys($result);
      echo "Renew sub ids";
      print_r($free_sub_entitled_renew_sids);

      if (!empty($free_sub_entitled_renew_sids)) {
        $RenewSubscriptionController = new RenewSubscriptionController;
        $response = $RenewSubscriptionController->renew_subscription($free_sub_entitled_renew_sids);
      }

    }else{
      echo "No subscription to process. !!!"; die('');
    }
    
    }else{
     echo "No subscription to process. !!"; die('');
    }

    die("Cron Finished");
  // return $arrayName = array('#markup' => 'Done' );
  }catch (Exception $e) {
           drupal_set_message($e."Some error occured","error");
         }

        }else{
          echo "You are not authorised to access this page.";
        }
    }

 }