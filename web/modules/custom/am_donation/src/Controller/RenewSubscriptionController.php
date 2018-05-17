<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_donation\Controller\UpdateAddressController;

class RenewSubscriptionController extends ControllerBase {

 public function renew_subscription($sids){

  // Get API details
  $config = \Drupal::config('am_sfs_integration.sfssettings');
  $API_URL = $config->get('sfs_renew_subscription_url');
  $APIKey_Value = $config->get('sfs_api_key_value');

if (!empty($sids)) {

  // For each sid, call renew subscription
  foreach ($sids as $key => $sid) {

    try{

  // Get PSN for SID
    $customerId = $this->get_customerId($sid);

  // Get email
    $email = $this->get_email($sid);

    $json = array(
            //"PromotionKey" => "9ASOC1",
			"PromotionKey" => "TASSOC1",
            "CustomerID" => $customerId,
            // "CustomerID" => '10261754',
            "Term" => 28,
            "Units" => 1,
            "UnitRate" => 60.00,
            "Key"=> $APIKey_Value,
        );
        $data_string = json_encode($json);
        $ch = curl_init($API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        // Execute Curl
        $response = curl_exec($ch);
        // $response_array = json_decode($result,true);
        // echo $result;
    // $response = '{"valid" : true,"message" : "Success"}';

    $this->update_renew_subscription_response($sid,$response);

    $response_array = ((array)json_decode($response));
    $response_array = array_map('strtolower', $response_array);
	
	/*****@AN@RenewSub******************/
		
		$request_values = array(            
            "customerid" => $customerId,
            "term" => 28,
            "units" => 1,
            "unitrate" => 60.00,
           //"promotionkey" => "9ASOC1",
		    "promotionkey" => "TASSOC1",
			"key" => $APIKey_Value,
			"responsestatus" => $response,
        );
		
		$webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_renewsub',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
		
	/*************End************/

    if ($response_array['valid'] == '1' && $response_array['message'] == 'success') {
      $this->is_sfs_renew_success($sid,'1');
      // Try to load user by email.
        $users = user_load_by_mail($email);

      $account = reset($users);

      if (!empty($account)) {
         // Provide donor and subscriber role.
        $user = \Drupal\user\Entity\User::load($account['uid']['x-default']);
        $user->addRole('subscriber');
        $user->addRole('donor');
      $user->save();
       }else {
         try{
            //Create a new user with provided email id.
           $CreateUserController = new CreateUserController;
           $value = $CreateUserController->createUser($email,'','',1,1);
         }catch (Exception $e) {
           drupal_set_message($e."Some create user error occured","error");
         }
       }

        // Update Address
        $UpdateAddressController = new UpdateAddressController;
        $response = $UpdateAddressController->update_address($sid,$customerId);
    }else{
      $this->is_sfs_renew_success($sid,'0');
    }

        $query = \Drupal::database()->update('webform_submission_data');
        $query->fields([
          'value' => '1'
        ]);
        $query->condition('name', 'is_processed_via_cron', '=');
        $query->condition('sid', $sid,'=');
        $query->execute();

    }catch (Exception $e) {
           drupal_set_message($e."Some error occured","error");
         }
    }
  }

}

public function update_renew_subscription_response($sid,$response){

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $response,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_renew_response');
    $query->execute();

    return true;
  }

  public function is_sfs_renew_success($sid,$status){
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $status,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_renew_success');
    $query->execute();

    return true;
 }

 public function get_customerId($sid){

    $query = \Drupal::database()->select('webform_submission_data', 'wsd');
    $query->fields('wsd', ['value']);
    $query->condition('wsd.sid', $sid,'=');
    $query->condition('wsd.name', 'print_subscription_number', '=');
    $customerId = $query->execute()->fetchField();

    return $customerId;
 }

 public function get_email($sid){

    $query = \Drupal::database()->select('webform_submission_data', 'wsd');
    $query->fields('wsd', ['value']);
    $query->condition('wsd.sid', $sid,'=');
    $query->condition('wsd.name', 'donor_email', '=');
    $email = $query->execute()->fetchField();

    return $email;
 }

 }
