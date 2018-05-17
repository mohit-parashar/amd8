<?php

namespace Drupal\latest_five_entity\Controller;

use Drupal\Core\Controller\ControllerBase;

class articleGetInsertDeltaController extends ControllerBase {

	// Get the login counts.
  public function getDelta($para_id) {

    $query = \Drupal::database()->select('paragraph__field_lfa_title', 'am');
    $query->addField('am', 'delta');
    $query->condition('am.entity_id', $para_id);
    $deltas = $query->execute()->fetchField();

    // echo "<pre>";var_dump($deltas);die();

    //return $count;

  }

}