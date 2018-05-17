<?php

namespace Drupal\latest_five_entity\Controller;

use Drupal\Core\Controller\ControllerBase;

class articleInsertController extends ControllerBase {

	public function insert() {
    
    $query = \Drupal::database()->insert('paragraph__field_lfa_title');
         $query->fields([
           'bundle',
           'deleted',
           'entity_id',
           'revision_id',
           'langcode',
           'delta',
           'field_lfa_title_target_id'
         ]);
         $query->values([
           'lfa_title_',
           '0',
           $para_id,
           $para_id,
           'en',
           $delta,
           $node_id
         ]);
     $result = $query->execute();
    
    return $result;
  }

}