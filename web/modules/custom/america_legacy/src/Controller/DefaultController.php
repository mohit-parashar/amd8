<?php

/**
 * @file
 * Contains \Drupal\america_legacy\Controller\DefaultController.
 */

namespace Drupal\america_legacy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Default controller for the america_legacy module.
 */
class DefaultController extends ControllerBase {

  public function america_legacy_handler($map, $key) {
// print "Map is $map\n";
// print "Key is $key\n";

    $req = \Drupal\Component\Utility\UrlHelper::parse(\Drupal::request()->getRequestUri());
//print_r($req);

    if (! empty ($req['query'])) {
      $params = $req['query'];
//print_r($params);
    }

    $table = "am_legacy_$map";

    switch ($map) {
      case 'article':
        $source = 'article_id';
        break;
      case 'blog':
      case 'blog/emailpost':
        // We have two forms of blog link, hence the double case, and
        // two forms of query string, I think because Todd changed
        // how the logic worked or something.
        if (isset($params['id'])) {
          // Long hexadecimal IDs
          $source = 'id';
          $table = 'am_legacy_blogentry';
        }
        elseif (isset($params['entry_id'])) {
          // Standard integer IDs
          $source = 'entry_id';
          $table = 'am_legacy_blog';
        }
        break;
      case 'signs':
        // For some reason we have two versions of this link
        if (isset($params['signid'])) {
          $source = 'signid';
        }
        elseif (isset($params['signID'])) {
          $source = 'signID';
        }
        break;
      case 'culture':
        // For some reason we have two versions of this link
        if (isset($params['cultureid'])) {
          $source = 'cultureid';
        }
        elseif (isset($params['cultureID'])) {
          $source = 'cultureID';
        }
        break;
      case 'podcast':
        $source = 'series_id';
        break;
      case 'blogcategory':
        // Note that this does not link to a TID but to the name of the blog
        $source = 'category_id';
        break;
      case 'static':
        // No DB lookup is necessary, so just set target directly.
//print "static map\n";
        $target = str_replace(':', '/', $key);
        if (! \Drupal::service('path.validator')->isValid("/$target")) {
          unset($target);
        }
//print "Target is $target\n";
        break;
      default:
        \Drupal::logger('america_legacy')->warning("No map found for %query", [
          '%query' => $req['path']
          ]);
          throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        break;
    }
//print "Source is $source\n";
    if (isset($source)) {
      if (isset($params[$source]) && db_table_exists ($table)) {
//print "Looking up " . $params[$source] . "in $table\n";
        $result = db_query("SELECT target from {$table} WHERE source = :source", [
          ':source' => $params[$source]
          ]);
        $target = $result->fetchColumn();
      }
    }
//print "Target is $target\n";

    if (isset($target) && !empty($target)) {
      $newpath = is_numeric($target) ? "/node/$target" : "/$target";
      $url = Url::fromUri("internal:$newpath", ['absolute' => TRUE]);
      $absURL = $url->toString();

//print "Attempting redirect to $absURL\n";
      $headers = ['Content-location' => $absURL];
      return new \Symfony\Component\HttpFoundation\RedirectResponse($newpath, 301, $headers);
    }
    else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
  }

}
