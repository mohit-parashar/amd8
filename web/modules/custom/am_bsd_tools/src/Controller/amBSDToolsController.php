<?php
/**
 * @file
 * Contains \Drupal\am_bsd_tools\Controller\amBSDToolsController.
 */
namespace Drupal\am_bsd_tools\Controller;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Controller routines for am_bsd_tools routes.
 */
class amBSDToolsController {

  public $bsd_env;
  public $bsd_env_print_subscription;
  public $bsd_env_is_subscriber;
  public function __construct() {
    if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
      if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
        $this->bsd_env = 47;
        $this->bsd_env_print_subscription = 48;
        $this->bsd_env_is_subscriber = 7;
      }
      else {
        $this->bsd_env = 10;
        $this->bsd_env_print_subscription = 11;
        $this->bsd_env_is_subscriber = 7;
      }
    }
  }


  /**
   * Get cons details from BSD system.
   * @author avk
   * @method getConstituentsById()
   * @param $consID
   * The constituent of the user in BSD system.
   * @param $groupID
   * The group id in the BSD system.
   * Use group ID as filter parameter.
   * @return group id on success or zero on fail.
   */
   public function getConstituentsById($consID, $groupID) {

        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_constituents_by_id');
        $api_key = $config->get('bsd_api_key_value');
        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'filter'  => 'cons_group=('.$groupID.')',
            'cons_ids' => $consID,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'filter'  => 'cons_group=('.$groupID.')',
                'cons_ids' => $consID,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => 2,
                'api_mac'  => $hash,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $xml = simplexml_load_string($contents);
        if(isset($xml->cons)) {
            return $groupID;
        } else {
            return '0';
        }

    }

  /**
   * Get Last contribution details.
   * @author avk
   * @method getLastContribution()
   * @param $consID
   * The constituent of the user in BSD system.
   * @return $json.
   * The constituent donation information in json format.
   */
   public function getLastContribution($consID) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_constituents_by_id');
        $api_key = $config->get('bsd_api_key_value');
        $loe = $config->get('loe');
        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'cons_ids' => $consID,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'cons_ids' => $consID,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $xml = simplexml_load_string($contents);

        $guid = $xml->cons->guid;
        $json = file_get_contents($loe.'/'.$guid);
        return $json;
    }

  /**
   * Get cons details from BSD system.
   * @author avk
   * @method getConstituentsByIdAjax().
   * Works for ajax call.
   * @param post array.
   * The array contains cons_id and group_id.
   * Use group ID as filter parameter.
   * @return group id on success or zero on fail.
   */
   public function getConstituentsByIdAjax() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_constituents_by_id');
        $api_key = $config->get('bsd_api_key_value');

        $consID = $_POST['cons_id'];
        $groupID = $_POST['group_id'];
        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'filter'  => 'cons_group=('.$groupID.')',
            'cons_ids' => $consID,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'filter'  => 'cons_group=('.$groupID.')',
                'cons_ids' => $consID,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => 2,
                'api_mac'  => $hash,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $xml = simplexml_load_string($contents);
        if(isset($xml->cons)) {
            print $groupID;die;
        } else {
            print '0';die;
        }

    }

  /**
   * Remove cons ID form spcific group.
   * @author avk
   * @method removeConsIdsFromGroup()
   * @param $consID
   * The constituent of the user in BSD system.
   * @param $groupID
   * The group id in the BSD system.
   * @return status code.
   */
    public function removeConsIdsFromGroup($consID, $groupID) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('remove_cons_ids_from_group');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'cons_group_id'  => $groupID,
            'cons_ids' => $consID,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );

        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'cons_group_id' => $groupID,
                'cons_ids' => $consID,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => 2,
                'api_mac'  => $hash,
            ],
        ]);

         return $response->getStatusCode();
    }

  /**
   * Remove cons ID form spcific group.
   * @author avk
   * @method removeConsIdsFromGroupAjax()
   * @param post array
   * The array contains cons_id and group_id.
   * @return status code.
   */
    public function removeConsIdsFromGroupAjax() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('remove_cons_ids_from_group');
        $api_key = $config->get('bsd_api_key_value');

        $consID = $_POST['cons_id'];
        $groupID = $_POST['group_id'];
        $time = time();
        $client = new GuzzleClient();

        $mac_array = array(
            'cons_group_id'  => $groupID,
            'cons_ids' => $consID,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );

        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
             $api_url,
             [
                'query' => [
                    'cons_group_id' => $groupID,
                    'cons_ids' => $consID,
                    'api_id'  => '1',
                    'api_ts'  => $time,
                    'api_ver'  => 2,
                    'api_mac'  => $hash,
                ],
        ]);

        print $response->getStatusCode();die;

    }

  /**
   * Get List of newslatter group from BSD system.
   * @author avk
   * @method listConstituentGroups()
   * It will return only public group from BSD system.
   * @return array of group.
   */
    public function listConstituentGroups() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('list_constituent_groups');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
            ],
        ]);
        $groups = array();
        $contents = $response->getBody()->getContents();
        $xmls = simplexml_load_string($contents);

        foreach ($xmls->cons_group as $key => $value) {
            $name = (string) $value->name;
            $str = (string) $value->description;
            $str = strtolower($str);
            $description = trim($str);
            // Get first 6 character from group description field.
            $description = substr($description, 0, 6);
            $id = (int) $value->attributes()->id;
            // Check the first 6 character is public or not.
            if($description == 'public'){
                $groups[$id] = $name;
            }
        }
        // Return result of group in array.
        return $groups;

    }

  /**
   * Get Cons ID by Email.
   * @author avk
   * @method getConstituentsByEmail()
   * @param $email.
   * The email address of the user to search in the BSD system.
   * @return Cons ID.
   */
   public function getConstituentsByEmail($email) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_constituents_by_email');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'emails' => $email,
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
                'emails' =>$email,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $xml = simplexml_load_string($contents);
        //print_r($xml);die;
        if(isset($xml->cons)) {
            $id = (int) $xml->cons->attributes()->id;
            return $id;
        } else {
            return '0';
        }

    }

  /**
   * Get Cons ID by Email.
   * @author avk
   * @method getConstituentsByEmail()
   * @param $email.
   * The email address of the user to search in the BSD system.
   * @return Cons ID.
   */
   public function getConstituentsInfoByEmail($email) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_constituents_by_email');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'emails' => $email,
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
                'emails' =>$email,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $xml = simplexml_load_string($contents);
        //print_r($xml);die;
        if(isset($xml->cons)) {
            $info = array();
            $firstname = $xml->cons->firstname;
            $lastname = $xml->cons->lastname;
            $info = array("fn"=>$firstname,"ln"=>$lastname);
            return $info;
        } else {
            return '0';
        }

    }

  /**
   * Set Cons details in the BSD system.
   * @author avk
   * @method setConstituentData()
   * @param $xmlObj.
   * The data in xml format to set in BSD system.
   * Update cons details by passing cons ID.
   * Create new cons if we does not pass cons ID.
   * @return Status code.
   */
    public function setConstituentData($xmlObj) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('set_constituent_data');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => 2,
        );

        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->post($api_url,array(
                'query' => [
                    'api_id'  => '1',
                    'api_ts'  => $time,
                    'api_ver'  => 2,
                    'api_mac'  => $hash,
                ],
                'Content-Type' => 'text/xml; charset=UTF8',
                'body'   => $xmlObj
            )
        );
        return $response->getStatusCode();
    }

  /**
   * Set Cons details in the BSD system.
   * @author avk
   * @method setConstituentDataAjax()
   * Used for ajax call.
   * @param Post array xml.
   * The data in xml format to set in BSD system.
   * Update cons details by passing cons ID.
   * Create new cons if we does not pass cons ID.
   * @return Status code.
   */
    public function setConstituentDataAjax() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('set_constituent_data');
        $api_key = $config->get('bsd_api_key_value');

        $xmlObj = trim(file_get_contents('php://input'));

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => 2,
        );

        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->post($api_url,array(
                'query' => [
                    'api_id'  => '1',
                    'api_ts'  => $time,
                    'api_ver'  => 2,
                    'api_mac'  => $hash,
                ],
                'Content-Type' => 'text/xml; charset=UTF8',
                'body'   => $xmlObj
            )
        );

        echo $response->getStatusCode();die;

    }

  /**
   * Register email in BSD system.
   * @author avk
   * @method emailRegister()
   * @param $email.
   * The email address of the user to be register in the BSD system.
   * @return Return cons id on success.
   */
    public function emailRegister($email) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('email_register');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'email' => $email,
            'is_subscribed' => '1',
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);
        try { 
            $response = $client->request('GET',
             $api_url,
             [
                'query' => [
                    'api_id'  => '1',
                    'api_ts'  => $time,
                    'api_ver'  => '2',
                    'api_mac'  => $hash,
                    'email' => $email,
                    'is_subscribed' => '1',
                ],
            ]);

           $contents = $response->getBody()->getContents();
           $xml = simplexml_load_string($contents);
           return $xml->cons_email->cons_id;
        }
        catch (RequestException $e) {
           return "error";
        }

    }

  /**
   * Register email in BSD system.
   * @author gga.
   * @method emailRegisterAjax()
   * Used for ajax call.
   * @param $email.
   * The email address of the user to be register in the BSD system.
   * @return Return cons id on success.
   */
    public function emailRegisterAjax() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('email_register');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'email' => $_POST['email'],
            );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
                'email' => $_POST['email'],
            ],
        ]);

       $contents = $response->getBody()->getContents();
       $xml = simplexml_load_string($contents);
       echo $xml->cons_email->cons_id;
       die;

    }

  /**
   * Delete email from BSD system for specific cons id.
   * @author avk
   * @method emailDelete()
   * @param $cons_id.
   * The cons ID of the user in the BSD system.
   * @param $email.
   * The Email of the user in the BSD system.
   * @return Return status code.
   */
    public function emailDelete($cons_id, $email) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('email_delete');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'cons_id' => $cons_id,
            'email' => $email,
            'api_id'  => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'cons_id' => $cons_id,
                'email' => $email,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => 2,
                'api_mac'  => $hash,
            ],
            'debug' => true
        ]);

       return $response->getStatusCode();

    }

  /**
   * Add email to specific cons id in the  BSD system.
   * @author avk
   * @method emailUpdate()
   * @param $cons_id.
   * The cons ID of the user in the BSD system.
   * @param $email.
   * The Email of the user in the BSD system.
   * @return Return cons ID.
   */
    public function emailUpdate($cons_id,$email) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('email_register');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'cons_id'=>$cons_id,
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'email' => $email,
        );

        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'cons_id'=>$cons_id,
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
                'email' => $email,
            ],
        ]);

           $contents = $response->getBody()->getContents();
           $xml = simplexml_load_string($contents);
           return $xml->cons_email->cons_id;

    }

  /**
   * Send triggered mail from BSD system.
   * @author avk
   * @method amBSDToolsSendTriggeredMail()
   * Create new cons for email address it does not exist in the BSD system.
   * @param $email.
   * The email id of to send mail.
   * @param $mailing_id.
   * The mailing ID that exist in the BSD system.
   * @return Return status code.
   */
   public function amBSDToolsSendTriggeredMail($email, $mailing_id) {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('send_triggered_email');
        $api_key = $config->get('bsd_api_key_value');
        $time = time();
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'mailing_id'=> $mailing_id,
            'email' => $email,
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
            [
                'query' => [
                    'api_id'  => '1',
                    'api_ts'  => $time,
                    'api_ver'  => '2',
                    'api_mac'  => $hash,
                    'mailing_id'=> $mailing_id,
                    'email' => $email,
                ],
        ]);

        return $response->getStatusCode();

  }

  /**
   * Generate mac to authenticate api call for BSD system.
   * @author avk
   * @method generateMac()
   *
   * @param $url.
   * The url of the api.
   * @param $query.
   * The array of parameter.
   * @param $secret.
   * The API secret to authonticate user.
   * @return Return hash code.
   */
   public function generateMac($url, $query, $secret) {
        // break URL into parts to get the path
        $urlParts = parse_url($url);

        // trim double slashes in the path
        if (substr($urlParts['path'], 0, 2) == '//') {
            $urlParts['path'] = substr($urlParts['path'], 1);
        }

        // build query string from given parameters
         $queryString = urldecode(http_build_query($query));

        // combine strings to build the signing string
         $signingString = $query['api_id'] . "\n" .
            $query['api_ts'] . "\n" .
            $urlParts['path'] . "\n" .
            $queryString;

        $mac = hash_hmac('sha1', $signingString, $secret);
        return $mac;
   }

  /**
   * Take payment from user and store it in BSD system.
   * @author gga.
   * @method donationChargeAjax()
   * Used for AJAX call
   * @param post array.
   * The array of parameter that will be used in donation api.
   * @return array of json.
   */
    public function donationChargeAjax() {

        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $url = $config->get('Charge');

        $fields = array(
                'slug' => $_POST['slug'],
                'firstname'=> $_POST['firstname'],
                'lastname'=> $_POST['lastname'],
                'addr1'=> $_POST['addr1'],
                'city'=> $_POST['city'],
                'state_cd'=> $_POST['state_cd'],
                'zip'=> $_POST['zip'],
                'country'=> $_POST['country'],
                'amount'=> $_POST['amount'],
                'amount_other'=> $_POST['amount_other'],
                'email'=> $_POST['email'],
                'phone'=> $_POST['phone'],
                'employer'=> $_POST['employer'],
                'occupation'=> $_POST['occupation'],
                'recurring_acknowledge'=> $_POST['recurring_acknowledge'],
                'cc_number'=>$_POST['cc_number'],
                'cc_expir_month'=>$_POST['cc_expir_month'],
                'cc_expir_year'=>$_POST['cc_expir_year']
            );

        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        curl_close($ch);

        $response_array = json_decode($result,true);
        echo $response_array;
        die('');
    }

  /**
   * Get metadata for recurring contributions.
   * @author avk
   * @method getRecurringContribution()
   * @param $token
   * The constituent of the user in BSD system.
   * @return $json.
   * The constituent donation information in json format.
   */
   public function getRecurringContribution() {
        // Get API details
        $config = \Drupal::config('am_bsd_tools.bsdtestsettings');
        // Check pantheon environment is live
        if(isset($_SERVER['PANTHEON_ENVIRONMENT'])){
          if($_SERVER['PANTHEON_ENVIRONMENT'] == 'live') {
            $config = \Drupal::config('am_bsd_tools.bsdlivesettings');
          }
        }
        $api_url = $config->get('get_by_token');
        $api_key = $config->get('bsd_api_key_value');

        $time = time();
        $token = $_GET['token'];
        //print "<pre>";print_r($api_url);print_r($token);die;
        $client = new GuzzleClient();
        $mac_array = array(
            'api_id' => '1',
            'api_ts' => $time,
            'api_ver' => '2',
            'charge_token'=> $token,
        );
        $queryString = urldecode(http_build_query($mac_array));

        $hash = $this->generateMac($api_url.'?'.$queryString, $mac_array, $api_key);

        $response = $client->request('GET',
         $api_url,
         [
            'query' => [
                'api_id'  => '1',
                'api_ts'  => $time,
                'api_ver'  => '2',
                'api_mac'  => $hash,
                'charge_token'=> $token,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        print "<pre>";var_dump($response);die;
        $xml = simplexml_load_string($contents);

        $guid = $xml->cons->guid;
        $json = file_get_contents($loe.'/'.$guid);
        return $json;
    }

}
