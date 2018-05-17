<?php

namespace Drupal\latest_five_entity\Controller;

use Drupal\Core\Controller\ControllerBase;

class articleExistController extends ControllerBase {

	public function exists($entity_id,$paragraph_id){

		$query = \Drupal::database()->select('paragraph__field_lfa_title', 'am');
	    $query->fields('am', ['field_lfa_title_target_id']);
	    $query->condition('am.field_lfa_title_target_id', $entity_id);
	    $query->condition('am.entity_id', $paragraph_id);
	    $query->range(0, 1);
	    $result = $query->execute()->fetchAssoc();

	    return $result;
	}

}