<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;

class UpdateAddressController extends ControllerBase {

 public function update_address($sid, $CustomerID){
  
 	// Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_update_address_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

if ($sid && $CustomerID) {  

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
            "CustomerID" => $CustomerID,
            // "CustomerID" => '10261754',
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
            "phone" => $result['donor_phone'],
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
        // $response = '{"valid" : true,"message" : "Success"}';

        $this->update_address_update_response($sid,$response);

        $response_array = ((array)json_decode($response));
        $response_array = array_map('strtolower', $response_array);
        
		/*****@AN@updateAddress ******************/
        $request_values = array(           
            "customerid" => $CustomerID,
            "firstname" => $result['first_name'],
            "lastname" => $result['last_name'],
            "company" => "",
            "address1" => $result['mailing_address_1'],
            "address2" => $result['mailing_address_2'],
            "city" => $result['mailing_city'],
            "stateprov" => $state,
            "zippostal" => $result['mailing_postal_code'],
            "countrycode" => $result['mailing_country'],
            "email" => $result['donor_email'],
            "phone" => $result['donor_phone'],
			"key" => $APIKey_Value,            		
			"responsestatus" => $response, 
        );

        $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_updateaddress',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*****@AN End******************/
		
		
        if ($response_array['valid'] == '1' && $response_array['message'] == 'success') {
          $this->is_sfs_address_update_success($sid,'1');
        }else{
            $this->is_sfs_address_update_success($sid,'0');
        }

        // Update and set if address register is called.
        $query = \Drupal::database()->update('webform_submission_data');
        $query->fields([
          'value' => '1'
        ]);
        $query->condition('name', 'is_sfs_address_updated', '=');
        $query->condition('sid', $sid,'=');
        $query->execute();

	}

 }


public function update_address_update_response($sid,$response){
    
    // Update Address Update response
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
        'value' => $response
    ]);
    $query->condition('name', 'sfs_address_update_response', '=');
    $query->condition('sid', $sid,'=');
    $query->execute();
    
    return true;
  }

  public function is_sfs_address_update_success($sid,$status){
    $query = \Drupal::database()->update('webform_submission_data');
    $query->fields([
      'value' => $status,
    ]);
    $query->condition('sid', $sid);
    $query->condition('name', 'is_sfs_address_update_success');
    $query->execute();
    
    return true;
 }

}