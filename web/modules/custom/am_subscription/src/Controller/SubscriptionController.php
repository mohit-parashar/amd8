<?php

namespace Drupal\am_subscription\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_donation\Controller\CreateUserController;
// BSD controllor
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;

class SubscriptionController extends ControllerBase {

 public function record_subscription(){

    // Is new user and will get a subscription ?
    $is_first_time_subscriber = FALSE;
    // Current date
    $current_date = date('Y-m-d');
    //Returns user id
    $uid = \Drupal::currentUser()->id();

    $custid = $_POST['custid'];
    $email = $_POST['email'];
    $orderid = $_POST['orderid'];
    $sfs_response = $_POST['sfs_response'];
    $first_name = $_POST['firstname'];
    $last_name = $_POST['lastname'];
    $created = time();

    $query = \Drupal::database()->insert('am_subscription');
         $query->fields([
           'uid',
           'custid',
           'email',
           'orderid',
           'sfs_response',
           'created',
         ]);
         $query->values([
           $uid,
           $custid,
           $email,
           $orderid,
           $sfs_response,
           $created,
         ]);
     $result = $query->execute();

     // Try to load user by email.
     $users = user_load_by_mail($email);

     $account = reset($users);
     if (empty($account)) {
        $CreateUserController = new CreateUserController;
        $response = $CreateUserController->createUser($email,$first_name,$last_name,0,1,$orderid);
        $is_first_time_subscriber = TRUE;
     }else{
        // Provide Subscriber role.
            $user = \Drupal\user\Entity\User::load($account['uid']['x-default']);
            $user->addRole('subscriber');
            $user->set("field_sfs_order_id", $orderid);
            $user->save();
     }

    // BSD Email register call
    $bsdClient = new amBSDToolsController();
    $cons_id = $bsdClient->emailRegister($email);

    if ($cons_id) {
      // Get user info, Firstname/Lastname
          $user_info = '';
          $user_info = $bsdClient->getConstituentsInfoByEmail($email);
          // Build XML
          // if ($user_info['fn']=='' || $user_info['ln']=='') {
                  $xml .= '<?xml version="1.0" encoding="utf-8"?>
                        <api>
                         <cons>';
            if ($user_info['fn'] == '') {
            $xml .= '<firstname>'.$first_name.'</firstname>';
          }
          if ($user_info['ln'] == '') {
            $xml .= '<lastname>'.$last_name.'</lastname>';
          }
          if ($is_first_time_subscriber) {
            $xml .= '<cons_field id="9">
                            <value>'.$current_date.'</value>
                        </cons_field>';
          }
          if ($user_info == 0 || $user_info == '0') {
            $xml .= '<cons_group id="15" />';
          }
            $xml .= '<is_banned>0</is_banned>
                        <has_account>1</has_account>
                        <cons_field id="7">
                            <value>Yes</value>
                        </cons_field>
                        <cons_field id="8">
                            <value>'.$current_date.'</value>
                        </cons_field>
                        <cons_email>
                            <email>'.$email.'</email>
                            <email_type>personal</email_type>
                            <is_subscribed>1</is_subscribed>
                            <is_primary>1</is_primary>
                        </cons_email>
                    </cons>
                  </api>';
          // Update user Firstname/Lastname
          $status_code = $bsdClient->setConstituentData($xml);

    }

    echo 'success';
    die();

 }

}
