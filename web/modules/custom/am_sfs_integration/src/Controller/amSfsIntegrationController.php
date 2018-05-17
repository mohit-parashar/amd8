<?php
/**
 * @file
 * Contains \Drupal\am_sfs_integration\Controller\amSfsIntegrationController.
 */
namespace Drupal\am_sfs_integration\Controller;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;

/**
 * Controller routines for am_sfs_integration routes.
 */
// class amSfsIntegrationController {

//     public function setSubscriptionData() {

//         $client = new GuzzleClient();

//         $json = '{
//             "FirstName": "arvind",
//             "LastName": "kinja",
//             "Company": "diaspark",
//             "Address1": "Address1",
//             "Address2": "",
//             "City": "indore",
//             "StateProv": "OH",
//             "ZipPostal": "12345",
//             "CountryCode": "US",
//             "Email": "arvind.kinja@diaspark.com",
//             "Term": 28,
//             "Units": 1,
//             "UnitRate":60.00,
//             "Key":"fP5tKivQ4He3Djml0ltvS4wQsLOoC8fzMddCZW9Mwp4a69Ye9VRltrNuKfIp",
//         }';
//     //$data_string = json_encode($json);
//         $response1 = $client->post('https://sfsdata.com/AMER/api/AmericaService/CreateSub',array(
//             'data'   => $data_string,
//             'Content-Type' => 'application/json',
//             'debug' => true,
//         ));

//     $ch = curl_init('https://sfsdata.com/AMER/api/AmericaService/CreateSub');
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//         'Content-Type: application/json',
//         'Content-Length: ' . strlen($json))
//     );

//     // Execute Curl
//     $result = curl_exec($ch);
//     $response_array = json_decode($result,true);
//     echo "<pre>";
//     var_dump($response_array);die;

//         echo "<pre>";
//             var_dump($response1);
//             echo $contents = $response1->getBody()->getContents();
//             echo "</pre>";
//         die;
//     }

class amSfsIntegrationController {

    public function setSubscriptionData() {

        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_set_subscription_data_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        $json = array(
            "FirstName" => $_POST['FirstName'],
            "LastName" => $_POST['LastName'],
            "Company" => $_POST['Company'],
            "Address1" => $_POST['Address1'],
            "Address2" => $_POST['Address2'],
            "City" => $_POST['City'],
            "StateProv" => $_POST['StateProv'],
            "ZipPostal" => $_POST['ZipPostal'],
            "CountryCode" => $_POST['CountryCode'],
            "Email" => $_POST['Email'],
            "Term" => 28,
            "Units" => 1,
            "UnitRate" =>60.00,
            "Key" => $APIKey_Value,
        );

        // $data_string = json_encode($json);
        // $ch = curl_init($API_URL);
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //     'Content-Type: application/json',
        //     'Content-Length: ' . strlen($data_string))
        // );

        // // Execute Curl
        // $result = curl_exec($ch);
        // $response_array = json_decode($result,true);
        //echo $result;
       echo $result='{
                        "valid" : true,
                        "message" : "Success",
                        "orderID" : "S00000"
                      }';

        // var_dump($response_array);
        die;

    }

    // public function verifyCustomerData() {

    //     $client = new GuzzleClient();

    //     $json = array(
    //         "CustomerID"=> 10100257,
    //         "Key"=>"fP5tKivQ4He3Djml0ltvS4wQsLOoC8fzMddCZW9Mwp4a69Ye9VRltrNuKfIp",
    //     );
    // $data_string = json_encode($json);
    // $ch = curl_init('https://sfsdata.com/AMER/api/AmericaService/CustData');
    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: application/json',
    //     'Content-Length: ' . strlen($data_string))
    // );

    // // Execute Curl
    // $result = curl_exec($ch);
    // $response_array = json_decode($result,true);
    // echo "<pre>";
    // var_dump($response_array); die;

    // }

    public function verifyCustomerData() {

        $customerId = $_POST['psn'];
        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_verify_customer_data_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        $json = array(
             "CustomerID"=> $customerId,
            //"CustomerID" => '10261754',
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
    $result = curl_exec($ch);
    $response_array = json_decode($result,true);
    echo $result;
//     echo $result='{
//     "isValid": true,
//     "First": "First",
//     "Last": "Last",
//     "Company": "Company",
//     "Address1": "Address1",
//     "Address2": "Address2",
//     "City": "City",
//     "Country": "US",
//     "State": "CA",
//     "Zip": "90210",
//     "Email": "name@email.com",
//     "Phone": "1234567890",
//     "AutoRenew": false,
//     "ExpireIssue": "01",
//     "ExpireYear": "2019",
//     "Status": "Active",
//     "GiftSubs":
//         [{
//             "CustomerID": "00000001",
//             "Status": "Not Active"
//         }, {
//             "CustomerID": "00000002",
//             "Status": "Active"
//         }, {
//             "CustomerID": "00000003",
//             "Status": "Not Active"
//         }, {
//             "CustomerID": "00000004",
//             "Status": "Not Active”
//         }]
// }';
    die;

    }

    public function getCustomerData($customerId) {

        //$customerId = $_POST['psn'];
        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_verify_customer_data_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        $json = array(
            //"PromotionKey"=>"9ASOC1",
            "CustomerID"=> $customerId,
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
        $result = curl_exec($ch);
		
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
		
        /*$result = '{
        "isValid": true,
        "First": "First",
        "Last": "Last",
        "Company": "Company",
        "Address1": "Address1",
        "Address2": "Address2",
        "City": "City",
        "Country": "US",
        "State": "CA",
        "Zip": "90210",
        "Email": "name@email.com",
        "Phone": "1234567890",
        "AutoRenew": true,
        "ExpireIssue": "20",
        "ExpireYear": "2019",
        "Status": "Active",
        "GiftSubs":
            [{
                "CustomerID": "00000001",
                "Status": "Not Active"
            }, {
                "CustomerID": "00000002",
                "Status": "Active"
            }, {
                "CustomerID": "00000003",
                "Status": "Not Active"
            }, {
                "CustomerID": "00000004",
                "Status": "Not Active"
            }, {
                "CustomerID": "00000004",
                "Status": "Not Active"
            }]
        }';*/
        $response_array = json_decode($result,true);
		
	
		
        return $response_array;

    }

    // public function verifyCustomerNumber() {

    //     $client = new GuzzleClient();

    //     $json = array(
    //         "CustomerID"=> 10100257,
    //         "Key"=>"fP5tKivQ4He3Djml0ltvS4wQsLOoC8fzMddCZW9Mwp4a69Ye9VRltrNuKfIp",
    //     );
    // $data_string = json_encode($json);
    // $ch = curl_init('https://sfsdata.com/AMER/api/AmericaService/VerifyCust');
    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: application/json',
    //     'Content-Length: ' . strlen($data_string))
    // );

    // // Execute Curl
    // $result = curl_exec($ch);
    // $response_array = json_decode($result,true);
    // echo "<pre>";
    // var_dump($response_array); die;

    // }

    public function verifyCustomerNumber() {

        $customerId = $_POST['psn'];
        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_verify_customer_number_url');
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
        $status = $response_array['valid'];
		
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

        echo $status;
        die();

    }

    // public function renewSubscription() {

    //     $client = new GuzzleClient();

    //     $json = array(
    //         //"PromotionKey" => "9HOLIDAY",
    //         "CustomerID" => 10100257,
    //         "Term" => 28,
    //         "Units" => 1,
    //         "UnitRate" => 60.00,
    //         "Key"=>"fP5tKivQ4He3Djml0ltvS4wQsLOoC8fzMddCZW9Mwp4a69Ye9VRltrNuKfIp",
    //     );
    //     $data_string = json_encode($json);
    //     $ch = curl_init('https://sfsdata.com/AMER/api/AmericaService/RenewSub');
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //         'Content-Type: application/json',
    //         'Content-Length: ' . strlen($data_string))
    //     );

    //     // Execute Curl
    //     $result = curl_exec($ch);
    //     $response_array = json_decode($result,true);
    //     echo "<pre>";
    //     var_dump($response_array); die;

    // }

    public function renewSubscription() {
        $customerId = $_POST['psn'];
        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_renew_subscription_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        $json = array(
            //"PromotionKey" => "9HOLIDAY",
            "CustomerID" => $customerId,
            "Term" => 28,
            "Units" => 1,
            "UnitRate" => 60.00,
            "Key"=> $APIKey_Value,
        );
        /*$data_string = json_encode($json);
        $ch = curl_init($API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        // Execute Curl
        $result = curl_exec($ch);*/
        // $response_array = json_decode($result,true);
        echo $result='{
                        "valid" : true,
                        "message" : "Success"
                        }';
        // var_dump($response_array);
        die;
    }

    public function updateAddress() {

        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_update_address_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        $json = array(
            "CustomerID" => $_POST['CustomerID'],
            //"CustomerID" => '10261754',
            "FirstName" => $_POST['FirstName'],
            "LastName" => $_POST['LastName'],
            "Company" => "America Media",
            "Address1"=> $_POST['Address1'],
            "Address2" => $_POST['Address2'],
            "City" => $_POST['City'],
            "StateProv" => $_POST['StateProv'],
            "ZipPostal" => $_POST['ZipPostal'],
            "CountryCode" => $_POST['CountryCode'],
            "Email" => $_POST['Email'],
            "Phone" => $_POST['Phone'],
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
            "customerid" => $_POST['CustomerID'],
            "firstname" => $_POST['FirstName'],
            "lastname" => $_POST['LastName'],
            "company" => "America Media",
            "address1" => $_POST['Address1'],
            "address2" => $_POST['Address2'],
            "city" => $_POST['City'],
            "stateprov" => $_POST['StateProv'],
            "zippostal" => $_POST['ZipPostal'],
            "countrycode" => $_POST['CountryCode'],
            "email" => $_POST['Email'],
            "phone" => $_POST['Phone'],
			"key" => $APIKey_Value,            		
			"responsestatus" => $result, 
        );

        $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_updateaddress',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*****@AN End******************/
		
        print $response_array['valid'];die;
    }

    public function getCustByOrderNo($orderID) {

        // Get API details
        $config = \Drupal::config('am_sfs_integration.sfssettings');
        $API_URL = $config->get('sfs_retrieve_cust_url');
        $APIKey_Value = $config->get('sfs_api_key_value');

        // CURL request
        $data = array("OrderID" => $orderID, "Key" => $APIKey_Value);
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
        $response = curl_exec($ch);
	/*****@AN retrieve_cust******************/
        $request_values = array(                        
            "orderid" => $orderID, 
            "key" => $APIKey_Value,			
			"responsestatus" => $response, 
        );

        $webform_submission = \Drupal\webform\Entity\WebformSubmission::create([
          'webform_id' => 'sfs_retrievecust',
        ]);
        $webform_submission->setData($request_values);
        $webform_submission->save();
		
	/*****@AN End******************/

        // JSON Response
        return $response;
    }

}
