<?php

namespace Drupal\am_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\am_registration\Controller\CreateLoginLinkController;

class CreateUserController extends ControllerBase {

  public function createUser($name, $first_name = '', $last_name = '', $print_subscription_number = null) {
   
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$user = \Drupal\user\Entity\User::create();

	  // Mandatory.
	 	$pass = explode('@',$name);

		$user->setPassword('@a!m'.$pass[0].'!a@m');
		$user->enforceIsNew();
		$user->setEmail($name);
		$user->set("field_first_name", $first_name);
		$user->set("field_last_name", $last_name);
		$user->set("field_print_subscription_number", $print_subscription_number);
		if ($print_subscription_number != '') {
	 		$user->addRole('subscriber');
	 	}
		$user->setUsername($name);

		// Optional.
		//$user->set('init', 'email');
		//$user->set('langcode', $language);
		$user->set('preferred_langcode', $language);
		//$user->set('preferred_admin_langcode', $language);
		//$user->set('setting_name', 'setting_value');
		//$user->addRole('rid');
		$user->block();

		// Save user account.
		$result = $user->save();
		// No email verification required; log in user immediately.
		//_user_mail_notify('register_no_approval_required', $user);
		//user_login_finalize($user);

		$CreateLoginLinkController = new CreateLoginLinkController;
		$value = $CreateLoginLinkController->createLoginLink($user, $flag='');
			
		return $result;
  }

}