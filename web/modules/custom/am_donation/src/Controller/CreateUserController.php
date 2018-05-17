<?php

namespace Drupal\am_donation\Controller;

use Drupal\Core\Controller\ControllerBase;

class CreateUserController extends ControllerBase {

  public function createUser($name,$first_name='',$last_name='',$is_donor='',$is_subscriber='',$orderID='') {

   $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
	  $user = \Drupal\user\Entity\User::create();

	  // Mandatory.
	 $pass = explode('@',$name);

	 $user->setPassword('@a!m'.$pass[0].'!a@m');
 	 $user->enforceIsNew();
	 $user->setEmail($name);
	 $user->setUsername($name);

	 if ($first_name!='') {
	 	$user->set("field_first_name", $first_name);
	 }
	 if ($last_name!='') {
	 	$user->set("field_last_name", $last_name);
	 }
	 if ($is_donor!='') {
	 	$user->addRole('donor');
	 }
	 if ($is_subscriber!='') {
	 	$user->addRole('subscriber');
	 }
	 if ($orderID!='') {
		$user->set("field_sfs_order_id", $orderID);
	 }

	 $user->set('preferred_langcode', $language);
	 $user->activate();

	 // Save user account.
	 $result = array();
	 $result = $user->save();

    return $result;
  }

  public function registerDonorAjax() {

  	$name = $_POST['donor_email'];
  	$first_name = $_POST['first_name'];
  	$last_name = $_POST['last_name'];

  // Try to load user by email.
      $users = user_load_by_mail($_POST['donor_email']);

	  $account = reset($users);
	    // echo "<pre>";
	    // print_r($account);die();
	  if (empty($account)) {

	   	 $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
		 $user = \Drupal\user\Entity\User::create();

		  // Mandatory.
		 $pass = explode('@',$name);

		 $user->setPassword('@a!m'.$pass[0].'!a@m');
	 	 $user->enforceIsNew();
		 $user->setEmail($name);
		 $user->setUsername($name);

		 if ($first_name!='') {
		 	$user->set("field_first_name", $first_name);
		 }
		 if ($first_name!='') {
		 	$user->set("field_last_name", $last_name);
		 }

		 $user->addRole('donor');
		 $user->set('preferred_langcode', $language);
		 $user->activate();

		 // Save user account.
		 $result = array();
		 $result = $user->save();

	    echo "Donor register success"; die('');

	}else{
		// Provide donor role.
	    	$user = \Drupal\user\Entity\User::load($account['uid']['x-default']);
	    	$user->addRole('donor');
			$user->save();
		echo "Already exists, provided donor role"; die('');
	}
  }

}
