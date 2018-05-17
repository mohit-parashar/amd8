<?php

namespace Drupal\latest_five_entity\Controller;

use Drupal\Core\Controller\ControllerBase;

class articleDeleteController extends ControllerBase {

	public function delete($entity_id){
		
	$query = \Drupal::database()->delete('paragraph__field_lfa_title');
    $query->condition('field_lfa_title_target_id', $entity_id);
    $query->execute();
	
	$query = \Drupal::database()->delete('paragraph_revision__field_lfa_title');
    $query->condition('field_lfa_title_target_id', $entity_id);
    $query->execute();
		
	}

}