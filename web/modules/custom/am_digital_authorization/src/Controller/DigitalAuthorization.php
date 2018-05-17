<?php

namespace Drupal\am_digital_authorization\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DigitalAuthorization extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function authorization(Request $request) {
		//$request_data = $request->getContent();
		$uid = $request->get('uid');
		$password = $request->get('password');
		// Get user has from database.
		$hash = $this->getUser($uid);

		\Drupal::logger('am_digital_authorization')->notice($uid. '--' . $password); 
		if (!empty($uid) && !empty($password) && !empty($hash['hash']) && ($password == $hash['hash'])) {
			if (password_verify('americamagazine', $password)) {
		    echo 'Authenticated';
			} else {
			    echo 'Invalid';
			}	
		}
		else {
		  echo 'Invalid';
		}	
		exit();
  }

  /**
	 * Implements helper function.
	 * Get all perviously grenrated hash.
	 */
	function am_get_digital_authorization_user() {
		$connection = \Drupal::service('database');
	  $query = $connection->query("SELECT uid FROM {am_digital_authorization}");
		$records = $query->fetchAll();
		$uid = [];
		foreach ($records as $record) {
			$uid[] = $record->uid;
		}

		return $uid;
	}

	/**
	 * Implements helper function.
	 * Get all perviously grenrated hash.
	 */
	function getUser($uid) {
		if (empty($uid)) {
			return [];
		}
		$connection = \Drupal::service('database');
	  $query = $connection->query("SELECT hash FROM {am_digital_authorization} WHERE uid = :uid", array(':uid' => $uid));
		$records = $query->fetchAll();
		$hash = [];
		foreach ($records as $record) {
			$hash['hash'] = $record->hash;
		}

		return $hash;
	}

}
