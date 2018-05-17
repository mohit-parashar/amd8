<?php

namespace Drupal\am_registration\Controller\SFS;

use Drupal\Core\Controller\ControllerBase;

class VerifyUserSubscriptionNumber extends ControllerBase {

  public function verifyPSN($customerId) {

  	$config = \Drupal::config('am_sfs_integration.sfssettings');
  	$API_URL = $config->get('sfs_verify_customer_number_url');
  	$APIKey_Value = $config->get('sfs_api_key_value');
  	// Get API details
  	//$config = \Drupal::config('am_registration.sfssettings');
    //$API_URL = $config->get('sfs_api_url');
    //$APIKey_Value = $config->get('sfs_api_key_value');

    // CURL request
  	$data = array("CustomerID" => $customerId, "Key" => $APIKey_Value);                                                                    
  	$data_string = json_encode($data);                                                                            
  	$ch = curl_init($API_URL);                                                          
  	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                               
  	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                      
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                         
  	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
  	    'Content-Type: application/json',                                                                                
  	    'Content-Length: ' . strlen($data_string))                                                                       
  	);

  	// Execute Curl
  	$result = curl_exec($ch);
  	$response_array = json_decode($result,true);

    //-------------@AN@sfs_verifycust-------------------
			$request_values = array(
            //"api_url" => $API_URL,            
            "customerid" => $customerId,
			"key" => $APIKey_Value,
			"responsestatus" => $result, 
            );
		
			$webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
			  'webform_id' => 'sfs_verifycust',
			]);
			$webform_submission->setData($request_values);
			$webform_submission->save();
		
				
    //--------------@END------------------	
		
  	$status = $response_array['valid'];
     
      return $status;

  }

  /**
   * Helper function to verify pns and get customer data.
   */
  public function verifyPsnAndCustData($customerId) {

    $config = \Drupal::config('am_sfs_integration.sfssettings');
    $API_URL = $config->get('sfs_verify_customer_data_url');
    $APIKey_Value = $config->get('sfs_api_key_value');

    // CURL request
    $data = array("CustomerID" => $customerId, "Key" => $APIKey_Value);                                                                    
    $data_string = json_encode($data);                                                                            
    $ch = curl_init($API_URL);                                                          
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                               
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                      
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                         
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string))                                                                       
    );

    // Execute Curl
    $result = curl_exec($ch);
    $response_array = json_decode($result,true);
	
	/*****@AN SFS_CustData******************/
        $request_values = array(
            "customerid" => $customerId,
            "key" => $APIKey_Value,                   
			"responsestatus" => $result, 
        );

        $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_custdata',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*****@AN End******************/

    $status = $response_array['valid'];
    $message = $response_array['Message'];

    if ($status) {
      return $response_array;
    }
    elseif ($message == 'An error has occurred.') {
      return 0;
    }

  }

  /**
   * Helper function to update pns details.
   */
  public function updatePsnAddress($pns, $custData) {

    // Get API details
    $config = \Drupal::config('am_sfs_integration.sfssettings');
    $API_URL = $config->get('sfs_update_address_url');
    $APIKey_Value = $config->get('sfs_api_key_value');

    $json = array(
        "CustomerID" => $pns,
        "FirstName" => $custData['First'],
        "LastName" => $custData['Last'],
        "Company" =>  $custData['Company'],
        "Address1"=> $custData['Address1'],
        "Address2" => $custData['Address2'],
        "City" => $custData['City'],
        "StateProv" => $custData['State'],
        "ZipPostal" => $custData['Zip'],
        "CountryCode" => $custData['Country'],
        "Email" => $custData['Email'],
        "Phone" => $custData['Phone'],
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
    $result = curl_exec($ch);
    $response_array = json_decode($result,true);
	
	/*****@AN@updateAddress ******************/
        $request_values = array(           
            "customerid" => $pns,
            "firstname" => $custData['First'],
            "lastname" => $custData['Last'],
            "company" => $custData['Company'],
            "address1" => $custData['Address1'],
            "address2" => $custData['Address2'],
            "city" => $custData['City'],
            "stateprov" => $custData['State'],
            "zippostal" => $custData['Zip'],
            "countrycode" => $custData['Country'],
            "email" => $custData['Email'],
            "phone" => $custData['Phone'],
			"key" => $APIKey_Value,            		
			"responsestatus" => $result, 
        );

        $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_updateaddress',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*****@AN End******************/
    
    return $response_array['valid'];
  }

}
