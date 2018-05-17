<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_sfs_integration\Controller\amSfsIntegrationController;

class RetrieveCustomerNumber extends ControllerBase {

  public function retrieve_customer_number($account) {


    $orderId = $account->get('field_sfs_order_id')->value;
    
      if ($orderId != '') {
  
        $amSfsIntegrationController = new amSfsIntegrationController;
        $response = $amSfsIntegrationController->getCustByOrderNo($orderId);  
    
        // $response = '{"valid" : true,"message" : "Success","customerID" : "00000025"}';

        $response_array = ((array)json_decode($response));
                
        if ($response_array['isValid'] == 'true' && $response_array['Message'] == 'Success' && $response_array['customerID'] != '') {
          $account->set("field_print_subscription_number", $response_array['customerID']);
          $account->save();
        }
      }
    return true;
  }

 
}