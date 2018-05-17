Author: Gaurav Agrawal
Created: 22 Nov 2016
========================================================================================================================
Module Name: AM Email Login
Config Path: admin/config/am_registration/settings
========================================================================================================================
Mail Template:

Subject:
One-time Login details for [user:display-name] at [site:name]

Body:
[user:display-name],

You may now log in by clicking this link or copying and pasting it into your browser:

[user:one-time-login-url]

This link can only be used once to log in.

--  [site:name] team

========================================================================================================================
















 /**
   * {@inheritdoc}
   */
  public function emailLogin($uid,$randno,$login_hash) {
   // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance
   $connection = Database::getConnection();

    // Retrieves a PDOStatement object
    // http://php.net/manual/en/pdo.prepare.php
    $sth = $connection->select('am_registration', 'am')
        ->fields('am', array('randno', 'hash','created'));
        // ->condition('am.field_c', '10', '<');

    // Execute the statement
    $data = $sth->execute();

    // Get all the results
    $results = $data->fetchAll(\PDO::FETCH_OBJ);

    // Iterate results
    foreach ($results as $row) {
      echo "Field a: {$row->randno}, field b: {$row->hash}, field c: {$row->created}";
      $_created = $row->created;
      $_randno = $row->randno;
      $_login_hash = $row->hash;
    }

    $current_time = time();
    if(($current_time - $_created) < 0){
      drupal_set_message("One time link has expired");
      return new RedirectResponse('http://drupal822.dd:8083/');
    }else{
      $login_hash_status = strcmp($login_hash,$login_hash);
      if($login_hash_status && ($randno == $_randno)){
        // Login user to drupal and delete the one time url.
      }
    }

    return;

  }