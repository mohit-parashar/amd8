<?php
/**
 * @file
 */
 namespace Drupal\am_disable_comment\Controller;


 
/**
 * Provides route responses for the Example module.
 */
class DisableCommentController {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
 

		
	  $element = array(
       '#markup' => 'Comment Disable Test Page!',
       );
		
       return  $element;
    }  

	
}

?>