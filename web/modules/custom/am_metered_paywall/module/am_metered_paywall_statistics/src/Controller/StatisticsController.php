<?php

namespace Drupal\am_metered_paywall_statistics\Controller;

use Drupal\Core\Controller\ControllerBase;

class StatisticsController extends ControllerBase {
  
 	
 // public function insert_record(){

 //    $unique_cookie = $_POST['unique_cookie'];
 //    $first_threshold_metered_nodes = $_POST['first_threshold_metered_nodes'];
 //    $first_threshold_last_metered_node = $_POST['first_threshold_last_metered_node'];
 //    $time = time();

 //    $query = \Drupal::database()->insert('am_paywall_statistics');
 //         $query->fields([
 //           'unique_cookie',
 //           'first_threshold_metered_nodes',
 //           'first_threshold_last_metered_node',
 //           'created',
 //           'changed',
 //         ]);
 //         $query->values([
 //           $unique_cookie,
 //           $first_threshold_metered_nodes,
 //           $first_threshold_last_metered_node,
 //           $time,
 //           $time,
 //         ]);
 //     $result = $query->execute();
    
 //    echo $result;
 //    die();
 // }

  public function record(){

    $metered_nodes = explode(',', $_POST['nodes']);
    $event = $_POST['event'];
    $current_nid = $_POST['current_nid'];
    if ($event == 1) {
      // Paywall Registration
      $this->record_registration($metered_nodes,$current_nid);
    }else{
      // Paywall Abandon
      $this->record_abandon($metered_nodes,$current_nid);
    }
    
    echo "Paywall statistics recorded.";
    die();
 }

 public function record_registration($metered_nodes,$current_nid){

  $time = time();

  foreach ($metered_nodes as $key => $nid) {
      
      $query = \Drupal::database()->insert('am_paywall_statistics');
           $query->fields([
             'nid',
             'registration',
             'abandon',
             'registration_contribute',
             'abandon_contribute',
             'created',
           ]);
           $query->values([
             $nid,
             0,
             0,
             1,
             0,
             $time,
           ]);
       $result = $query->execute();
    }

    $this->record_registration_node($current_nid,$time);

    return 'success';
  
 }

 public function record_registration_node($current_nid,$time){

  $query = \Drupal::database()->insert('am_paywall_statistics');
           $query->fields([
             'nid',
             'registration',
             'abandon',
             'registration_contribute',
             'abandon_contribute',
             'created',
           ]);
           $query->values([
             $current_nid,
             1,
             0,
             0,
             0,
             $time,
           ]);
       $result = $query->execute();

    return;
 }

public function record_abandon($metered_nodes,$current_nid){

  $time = time();

  foreach ($metered_nodes as $key => $nid) {
      
      $query = \Drupal::database()->insert('am_paywall_statistics');
           $query->fields([
             'nid',
             'registration',
             'abandon',
             'registration_contribute',
             'abandon_contribute',
             'created',
           ]);
           $query->values([
             $nid,
             0,
             0,
             0,
             1,
             $time,
           ]);
       $result = $query->execute();
    }

    $this->record_abandon_node($current_nid,$time);

    return;
  
 }

 public function record_abandon_node($current_nid,$time){

  $query = \Drupal::database()->insert('am_paywall_statistics');
           $query->fields([
             'nid',
             'registration',
             'abandon',
             'registration_contribute',
             'abandon_contribute',
             'created',
           ]);
           $query->values([
             $current_nid,
             0,
             1,
             0,
             0,
             $time,
           ]);
       $result = $query->execute();


    return;
 }

 

}