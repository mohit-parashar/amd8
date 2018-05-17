<?php

/**
 * @file
 * Contains \Drupal\gdocs_publish\Controller\PageController class
 *
 * Default controller for the gdocs_publish module.
 */

namespace Drupal\gdocs_publish\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\filter\Entity\FilterFormat;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Drupal\Core\Url;

class PageController extends ControllerBase {

  /**
   * Display HTML page, defaulting to the about page
   *
   * @param string $page Filename of HTML page to display. Assumed
   *                     to be in docs directory.
   *
   * @returns string $html The page content
   */
  public function gdocs_publish_about($page = 'about') {
    $path = drupal_get_path('module', 'gdocs_publish');
    $html = file_get_contents("$path/docs/$page.html");
    return ['#markup' => $html];
  }

  /**
   * Publish a node from data posted by the Google Docs add-on
   *
   */
  public function gdocs_publish_publish() {
    $config = \Drupal::config('gdocs_publish.config');

    $method = \Drupal::request()->getMethod();
    if ($method != 'POST'){
      \Drupal\gdocs_publish\Logger\GDPLogger::log( 'error', 'publish bad method: %method', ['%method' => $method]);
      throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Method not allowed', null, 405);
    }

    $user = \Drupal::currentUser();

    $format = $config->get('gdocs_publish.format');

    $filter = FilterFormat::load($format);

    if (! $filter->access('use')) {
      \Drupal\gdocs_publish\Logger\GDPLogger::log('error', "Filter format error for format " . $format);
      drupal_set_message(t('You do not have permission to create content in this format.'), 'error');
      return new RedirectResponse(\Drupal::url('gdocs_publish.about_help'));
    }

    $params = \Drupal::request()->request->all();

    $content = check_markup($params['body-text'],$format);
    $type = $params['content-type'];

    $content_types = node_type_get_names();

    if (! array_key_exists($type, $content_types)) {
      drupal_set_message(t('Cannot create a node of type %t.', array ('%t'=>$type)), 'error');
      return new RedirectResponse(\Drupal::url('gdocs_publish.about_help'));
    }

    if (empty($params['title'])) {
      $title = '-- NO HEADLINE --';
    }
    else {
      $title = filter_var(
        $params['title'],
        FILTER_SANITIZE_STRING,
        FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
      );
    }

    $uid = $user->id();

    $fields = array (
      'gd_id' => $params['gdocs_id'],
      'gd_title' => $params['gdocs_name'],
      'gd_user' => $params['gdocs_user'],
      'content_type' => $type,
      'uid' => $uid,
      'import_date' => REQUEST_TIME,
    );

    try {
      $node = \Drupal::entityManager()->getStorage('node')->create(array(
        'type' => $params['content-type'],
        'title' => $title,
        'body' => [
          'value' => $content,
          'format' => $format
        ]
        ));
      $node->uid = $uid;
      $node->status = '0';
      if ($node->save()) {
        $fields['nid'] = $nid = $node->id();
        $fields['success'] = true;
        _db_insert($fields);
        return $this->redirect('entity.node.edit_form', ['node' => $nid]);
      }
      else {
        $fields['success'] = false;
        _db_insert($fields);
        drupal_set_message(t('An error occured when creating the node.'), 'error');
      }
    }
    catch (Exception $e) {
      $msg = $e->getMessage();
      $fields['success'] = false;
      $fields['false'] = true;
      _db_insert($fields);
      \Drupal::logger('gdocs_publish')->notice("Exception creating %name by %user: %msg (document ID %id)", array (
          '%user' => $fields['gd_user'],
          '%name' => $title,
          '%msg'  => $msg,
          '%id'   => $fields['gd_id']
        ));
      drupal_set_message(t("An exception occured when creating the node: $msg."), 'error');
    }
  }

  public function gdocs_publish_settings() {
    return ['#markup' => '<h1>Settings not implemented yet</h1>'];
  }
}

