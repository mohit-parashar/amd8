<?php

namespace Drupal\article_custom\Controller;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller routines for page example routes.
 */
class articleCustomAuhorBioController extends ControllerBase {

  /**
   * Constructs a page with descriptive content.
   *
   * Our router maps this method to the path 'examples/page-example'.
   */
  public function article_custom_author_bio($first) { 
    // Make sure you don't trust the URL to be safe! Always check for exploits.
    if (!is_numeric($first)) {
      // We will just show a standard "access denied" page in this case.
      throw new AccessDeniedHttpException();
    }
    //echo $first; die;
    $nodeObj = Node::load($first);
    $body = $nodeObj->body->getValue();
    //print "<pre>";print_r($nodeObj);

    print($body[0]['summary']); die;
  }

}
