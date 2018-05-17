<?php

namespace Drupal\am_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\am_registration\Controller\DeleteLinkController;
use Drupal\am_registration\Controller\LoginCountsController;
use Symfony\Component\HttpFoundation\RedirectResponse;
// BSD controllor
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;
/**
 * An example controller.
 */
class EmailLoginController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function emailLogin($uid,$randno,$login_hash) {
   // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance
   $connection = Database::getConnection();

    // Retrieves a PDOStatement object
    // http://php.net/manual/en/pdo.prepare.php
    $sth = $connection->select('am_registration', 'am')
        ->fields('am', array('uid','randno', 'hash','created','mail'))
        ->condition('am.uid', $uid, '=');

    // Execute the statement
    $data = $sth->execute();

    // Get all the results
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    if(count($results) == 0 ){
       drupal_set_message('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new login link by clicking the "Log In" link in the menu bar.',"error");
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    // Iterate results
    foreach ($results as $row) {
      //echo "Field a: {$row->randno}, field b: {$row->hash}, field c: {$row->created}";
      $_created = $row->created;
      $_randno = $row->randno;
      $_login_hash = $row->hash;
      $_mail = $row->mail;
      $_uid = $row->uid;
    }

    // Get current timestamp
    $current_time = time();

    // Check if link has expired. Current time is set to 24 hours.
    if(($current_time - $_created) > 86400){
      drupal_set_message('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new login link by clicking the "Log In" link in the menu bar.',"error");
      return new RedirectResponse(\Drupal::url('<front>'));
    }else{
      
      $login_hash_status = strcmp($login_hash,$_login_hash);
      if(($login_hash_status == 0) && ($randno == $_randno)){
        // $user = user_load_by_mail($name);
        // pass your uid
        $account = \Drupal\user\Entity\User::load($_uid);
        $account->activate();
        $account->set('field_is_email_validate', '1');
        // The crucial part! Save the $user object, else changes won't persist.
        $account->save();

        user_login_finalize($account);

        // Login user to drupal and delete the previous one time url.
        $delete_result = new DeleteLinkController;
        $value = $delete_result->delete($_uid);

        //One time Login counts
          $LoginCountsController = new LoginCountsController;
          $status = $LoginCountsController->exists($_mail);
          if($status == FALSE){
            $result = $LoginCountsController->insert($_mail,$_uid);
          }else{
            $login_count = $LoginCountsController->getCount($_mail);
            $LoginCountsController->updateCount($_mail,$login_count);
          }
        // Set Daily Newsletter group in bsd system.
        $bsdClient = new amBSDToolsController(); 
        $xml = '<?xml version="1.0" encoding="utf-8"?>
          <api>
            <cons>
                <cons_field id="'.$bsdClient->bsd_env.'">
                  <value>Yes</value>
                </cons_field>               
                <cons_group id="15" />
                <cons_email>
                  <email>'.$_mail.'</email>
                    <email_type>personal</email_type>
                    <is_subscribed>1</is_subscribed>
                    <is_primary>1</is_primary>
                  </cons_email>
            </cons>
          </api>';
          $bsdClient->setConstituentData($xml);
          
        drupal_set_message(t('Hello @user, You have just used your one-time login link.', array('@user' => $_mail)));
        return new RedirectResponse('/user');
      }else{
        drupal_set_message("Invalid login Link. Please request a new login link.","error");
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }

   

  }

/**
 * Email Update
 */
  public function emailUpdate($uid,$randno,$login_hash) {
   // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance
   $connection = Database::getConnection();

    // Retrieves a PDOStatement object
    // http://php.net/manual/en/pdo.prepare.php
    $sth = $connection->select('am_registration', 'am')
        ->fields('am', array('uid','randno', 'hash','created','mail'))
        ->condition('am.uid', $uid, '=');

    // Execute the statement
    $data = $sth->execute();

    // Get all the results
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    if(count($results) == 0 ){
       drupal_set_message("You have tried to use a email confirmation link that has either been used or is no longer valid. Please request a new login link.","error");
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    // Iterate results
    foreach ($results as $row) {
      //echo "Field a: {$row->randno}, field b: {$row->hash}, field c: {$row->created}";
      $_created = $row->created;
      $_randno = $row->randno;
      $_login_hash = $row->hash;
      $_mail = $row->mail;
      $_uid = $row->uid;
    }

    // Get current timestamp
    $current_time = time();

    // Check if link has expired. Current time is set to 24 hours.
    if(($current_time - $_created) > 86400){
      drupal_set_message("You have tried to use a email verification link that has either been used or is no longer valid. Please request a new login link.","error");
      return new RedirectResponse(\Drupal::url('<front>'));
    }else{
      
      $login_hash_status = strcmp($login_hash,$_login_hash);
      if(($login_hash_status == 0) && ($randno == $_randno)){
        // $user = user_load_by_mail($name);
        $account = \Drupal\user\Entity\User::load($_uid); // pass your uid
        $account->activate();
        $account->set('field_is_email_validate', '1');
        $account->save();
        user_login_finalize($account);

        // Login user to drupal and delete the previous one time url.
        $delete_result = new DeleteLinkController;
        $value = $delete_result->delete($_uid);

        //One time Login counts
          $LoginCountsController = new LoginCountsController;
          $status = $LoginCountsController->exists($_mail);
          if($status == FALSE){
            $result = $LoginCountsController->insert($_mail,$_uid);
          }else{
            $login_count = $LoginCountsController->getCount($_mail);
            $LoginCountsController->updateCount($_mail,$login_count);
          }
        // Set Daily Newsletter group in bsd system.
        $bsdClient = new amBSDToolsController(); 

        $old_email = $account->get('mail')->value;
        $new_email = $account->get('field_email_verify')->value;
        $consID = $account->get('field_cons_id')->value;
        

        //we have verify that user has verified his new email successfully and hence we are updating his old email with new email.
        if ($old_email != '' && $new_email != '' && $old_email != $new_email) {
          // now we are again checking whether the email is available in drupal or not
          if(user_load_by_mail($new_email)) {
            //if available then  we will not update it
            drupal_set_message(t('Account already exist with email @user address.', array('@user' => $new_email)));
          } else {
            //if not available then we will update
            $account->set("mail", $new_email);
            $account->set("name", $new_email);
            $account->set("field_email_verify", "");
            $users_update = $account->save();
        
            drupal_set_message(t('Hello @user, You have just confirmed your email address.', array('@user' => $_mail)));
             // and hence we have changed email successsfully in drupal we are also updating bsd and removing his orignal email. 
            if($consID != '') {
                $xml = '<?xml version="1.0" encoding="utf-8"?>
                          <api>
                            <cons id="'.$consID.'">
                              <cons_email>
                                <email>'.$new_email.'</email>
                              </cons_email>
                            </cons>
                        </api>';
              // Remove old email address from BSD system.
              $bsdClient->emailDelete($consID, $old_email);
              // Set new email address in BSD system.
              $bsdClient->setConstituentData($xml);          
            }
          }
      
        }

        return new RedirectResponse('/user');
      }else{
        drupal_set_message("Invalid email verification Link.","error");
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }
  }

/**
 * Email Update
 */
  public function newsletterUpdate($uid,$randno,$login_hash) {
   // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance
   $connection = Database::getConnection();

    // Retrieves a PDOStatement object
    // http://php.net/manual/en/pdo.prepare.php
    $sth = $connection->select('am_registration', 'am')
        ->fields('am', array('uid','randno', 'hash','created','mail'))
        ->condition('am.uid', $uid, '=');

    // Execute the statement
    $data = $sth->execute();

    // Get all the results
    $results = $data->fetchAll(\PDO::FETCH_OBJ);
    if(count($results) == 0 ){
       drupal_set_message("You have tried to use a subscription link that has either been used or is no longer valid. Please request a new link.","error");
      return new RedirectResponse('/');
    }
    // Iterate results
    foreach ($results as $row) {
      //echo "Field a: {$row->randno}, field b: {$row->hash}, field c: {$row->created}";
      $_created = $row->created;
      $_randno = $row->randno;
      $_login_hash = $row->hash;
      $_mail = $row->mail;
      $_uid = $row->uid;
    }

    // Get current timestamp
    $current_time = time();

    // Check if link has expired. Current time is set to 24 hours.
    if(($current_time - $_created) > 86400){
      drupal_set_message("You have tried to use a email subscription link that has either been used or is no longer valid. Please request a new subscription link.","error");
      return new RedirectResponse(\Drupal::url('<front>'));
    }else{
      
      $login_hash_status = strcmp($login_hash,$_login_hash);
      if(($login_hash_status == 0) && ($randno == $_randno)){
        // $user = user_load_by_mail($name);
        $account = \Drupal\user\Entity\User::load($_uid); // pass your uid
        $account->activate();
        $account->set('field_is_email_validate', '1');
        $account->save();
        user_login_finalize($account);

        // Login user to drupal and delete the previous one time url.
        $delete_result = new DeleteLinkController;
        $value = $delete_result->delete($_uid);

        //One time Login counts
          $LoginCountsController = new LoginCountsController;
          $status = $LoginCountsController->exists($_mail);
          if($status == FALSE){
            $result = $LoginCountsController->insert($_mail,$_uid);
          }else{
            $login_count = $LoginCountsController->getCount($_mail);
            $LoginCountsController->updateCount($_mail,$login_count);
          }

        // Set Daily Newsletter group in bsd system.
        $bsdClient = new amBSDToolsController(); 

        $consID = $account->get('field_cons_id')->value;

        //$account->set("mail", $new_email);
        
        if($consID != '') {
            // Set Daily Newsletter group in bsd system.
            $xml = '<?xml version="1.0" encoding="utf-8"?>
              <api>
                <cons id="'.$consID.'">
                    <cons_email>
                      <email>'.$_mail.'</email>
                        <email_type>personal</email_type>
                        <is_subscribed>1</is_subscribed>
                        <is_primary>1</is_primary>
                        <cons_field id="'.$bsdClient->bsd_env.'">
                        <value>Yes</value>
                      </cons_field>
                      </cons_email>
                    <cons_group id="15" />
                </cons>
              </api>';
              $bsdClient->setConstituentData($xml);
        } else {
             $field_cons_id = $bsdClient->getConstituentsByEmail($account->get('mail')->value);
             $account->set("field_cons_id", $field_cons_id);
             $userss = $account->save();
            $xml = '<?xml version="1.0" encoding="utf-8"?>
              <api>
                <cons id="'.$field_cons_id.'">
                    <cons_email>
                      <email>'.$_mail.'</email>
                        <email_type>personal</email_type>
                        <is_subscribed>1</is_subscribed>
                        <is_primary>1</is_primary>
                      </cons_email>
                    <cons_group id="15" />
                    <cons_field id="'.$bsdClient->bsd_env.'">
                    <value>Yes</value>
                  </cons_field>
                </cons>
              </api>';
              $bsdClient->setConstituentData($xml);
        }

        drupal_set_message(t('@user was subscribed to the Daily Newsletter mailing list.', array('@user' => $_mail)));
        return new RedirectResponse('/user');
      }else{
        drupal_set_message("Invalid subscription Link.","error");
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }
  }

}

