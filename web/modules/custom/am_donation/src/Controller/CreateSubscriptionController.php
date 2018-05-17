<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_donation\Controller\CreateUserController;

class CreateSubscriptionController extends ControllerBase {

 public function create_subscription($sids){

 	// Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_set_subscription_data_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

if (!empty($sids)) {
 	// For each sid, call create subscription
  foreach ($sids as $key => $sid) {

  	try{

        $query = \Drupal::database()->select('webform_submission_data', 'wsd');
    	$query->fields('wsd', ['name', 'value']);
    	$query->condition('wsd.sid', $sid,'=');
    	$result = $query->execute()->fetchAllKeyed();

    	$state = '';
    	if ($result['mailing_country'] == "US") {
    		$state = $result['mailing_state'];
    	}elseif ($result['mailing_country'] == "CA") {
    		$state = $result['mailing_province'];
    	}else{
    		$state = $result['mailing_state_province'];
    	}

        $json = array(
            "FirstName" => $result['first_name'],
            "LastName" => $result['last_name'],
            "Company" => '',
            "Address1" => $result['mailing_address_1'],
            "Address2" => $result['mailing_address_2'],
            "City" => $result['mailing_city'],
            "StateProv" => $state,
            "ZipPostal" => $result['mailing_postal_code'],
            "CountryCode" => $result['mailing_country'],
            "Email" => $result['donor_email'],
            "Term" => 28,
            "Units" => 1,
            "UnitRate" => 60.00,
            //"PromotionKey" => "9ASOC1",
			"PromotionKey" => "TASSOC1",
            "Key" => $APIKey_Value,
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
        // $response = '{"valid" : true,"message" : "Success","orderID" : "S00000"}';

        $this->update_create_subscription_response($sid,$response);

        $response_array = ((array)json_decode($response));
		
	/*****@AN@sfs_createsub******************/
		$request_values = array(            
            "firstname" => $result['first_name'],
            "lastname" => $result['last_name'],
            "company" => '',
            "address1" => $result['mailing_address_1'],
            "address2" => $result['mailing_address_2'],
            "city" => $result['mailing_city'],
            "stateprov" => $state,
            "zippostal" => $result['mailing_postal_code'],
            "countrycode" => $result['mailing_country'],
            "email" => $result['donor_email'],
            //"phone" => $result['phone'],			
            "term" => 28,
            "units" => 1,
            "unitrate" => 60.00,
           // "promotionkey" => "9ASOC1",
		    "promotionkey" => "TASSOC1",
			"key" => $APIKey_Value,
			"responsestatus" => $response, 
        );
		
		$webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_createsub',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*************End************/
		
		
        // $response_array = array_map('strtolower', $response_array);
        if ($response_array['valid'] == '1' && $response_array['message'] == 'Success') {
          $this->is_sfs_create_success($sid,'1');
          $this->update_sfs_order_id($sid,$response_array['orderID']);

          // Try to load user by email.
            $users = user_load_by_mail($result['donor_email']);

            $account = reset($users);

            if (!empty($account)) {
               // Provide donor and subscriber role.
                $user = \Drupal\user\Entity\User::load($account['uid']['x-default']);
                $user->addRole('subscriber');
                $user->addRole('donor');
                $user->set("field_sfs_order_id", $response_array['orderID']);
                $user->save();
             }else {
               try{
                  //Create a new user with provided email id.
                 $CreateUserController = new CreateUserController;
                 $value = $CreateUserController->createUser($result['donor_email'],$result['first_name'],$result['last_name'],1,1,$response_array['orderID']);
               }catch (Exception $e) {
                 drupal_set_message($e."Some create user error occured","error");
               }
             }
        }else{
          $this->is_sfs_create_success($sid,'0');
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

	return $arrayName = array('#markup' => 'Done!');
 }


 public function update_create_subscription_response($sid,$response){

    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $response,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_create_response');
    $query->execute();

    return true;
  }

 public function is_sfs_create_success($sid,$status){
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $status,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_create_success');
    $query->execute();

    return true;
 }

 public function update_sfs_order_id($sid,$orderID){
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $orderID,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'sfs_order_id');
    $query->execute();

    return true;
 }

}
