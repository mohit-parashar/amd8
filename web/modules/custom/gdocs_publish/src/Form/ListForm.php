<?php

/**
 * @file
 * Contains \Drupal\gdocs_publish\Form\ListForm
 */

namespace Drupal\gdocs_publish\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Render\FormattableMarkup;

class ListForm extends FormBase {

 /**
  * {@inheritdoc}
  */
  public function getFormId () {
    return 'gdp_form';
  }

 /**
  * {@inheritdoc}
  */
  public function validateForm (array &$form, FormStateInterface $form_state) {
  }

 /**
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    if ($user->hasPermission('administer gdocs publish')) {
      switch ($form_state->getValue('op')) {
        case 'Unpublish':
          drupal_set_message("You have permission to unpublish nodes, but this feature is not yet implemented.", 'warning');
          break;
        case 'Delete':
          drupal_set_message("You have permission to delete nodes, but this feature is not yet implemented.", 'warning');
          break;
        default:
          drupal_set_message("Invalid operation", 'error');
      }
    }
    else {
      drupal_set_message(t("You do not have permission to perform this operation."), 'error');
      \Drupal::logger('gdocs_publish')->warning("Attempted %o by %u", [
        '%o' => $form_state->getValue('op'),
        '%u' => $user->getUsername()
      ]);
    }
  }

 /**
  * {@inheritdoc}
  */
  public function buildForm (array $form, FormStateInterface $form_state) {

    // Set up filters
    $active_filters = Array();
    if ($form_state->getValue('op') == 'Filter') {
      $values = $form_state->getValues();
      $active_filters = array_filter($values, function ($k) {return strpos($k, 'filter_') === 0;}, ARRAY_FILTER_USE_KEY);
    }

    // Populate filter lists with values actually present in the table
    try {
      $q_gd_user = db_select('gdocs_publish', 'g')->fields('g', array('gd_user'))->groupBy('gd_user')->execute();
      $q_drupal_user = db_select('gdocs_publish', 'g')->fields('g', array('uid'))->groupBy('uid')->execute();
      $q_gd_types = db_select('gdocs_publish', 'g')->fields('g', array('content_type'))->groupBy('content_type')->execute();
    }
    catch (Exception $e) {
      $msg = $e->getMessage();
      \Drupal::logger('gdocs_publish')->notice("DB exception: %m", array('%m' => $msg));
      drupal_set_message(t("Database exception: $msg."), 'error');
    }

    // Add the default no-filter option, then populate Google user filter list
    $gd_users[0] = 'ALL';

    foreach ($q_gd_user->fetchCol() as $u) {
      $gd_users[$u] = $u;
    }

    // Drupal user filter list
    $d_users = $q_drupal_user->fetchCol();

    $drupal_users = Array();
    foreach ($d_users as $u) {
      $user = \Drupal\user\Entity\User::load($u);
      if ($user) {
        $drupal_users[$u] = $user->getUsername();
      }
    }
    array_unshift($drupal_users, 'ALL');

    // Content type filter list
    $content_type_labels = node_type_get_names();
    $content_type_labels['INVALID OR DELETED'] = "N/A";

    foreach ( $q_gd_types->fetchCol()  as $t ) {
      $content_types[$t] =  $content_type_labels[$t];
    }

    array_unshift($content_types, 'ALL');

    // Build form
    $form['filters'] = array (
      '#type' => 'details',
      '#title' => t('Filter imported articles'),
      '#open' => FALSE,
    );

    // Filter controls all begin with filter_
    $form['filters']['filter_uid'] = [
      '#type'     => 'select',
      '#options'  => $drupal_users,
      '#title'    => t('Drupal user')
    ];

    $form['filters']['filter_gd_user'] = [
      '#type'     => 'select',
      '#options'  => $gd_users,
      '#title'    => t('Google user')
    ];

    $form['filters']['filter_content_type'] = [
      '#type'     => 'select',
      '#options'  => $content_types,
      '#title'    => t('Content type')
    ];

    $form['filters']['submit_button'] = [
      '#type'   => 'button',
      '#value'  => 'Filter'
    ];

    // Content provided by separate method.
    $form['list'] = \Drupal\gdocs_publish\Form\ListForm::listPublished($active_filters);

    $form['pager'] = [
      '#type' => 'pager',
    ];

    $form['delete_button'] = [
      '#type'   => 'submit',
      '#value'  => 'Delete'
    ];

    $form['unpublish_button'] = [
      '#type'   => 'submit',
      '#value'  => 'Unpublish'
    ];

    return $form;
  }

  /**
   * Return tableselect element containing nodes published from Google Docs
   */
  public function listPublished($filters) {

    $items_per_page = \Drupal::config('gdocs_publish.config')->get('items_per_page') ?: 25;

    try {
      $query = db_select('gdocs_publish', 'g');

      // If there are active filters, apply them here
      if (! empty ($filters)) {
        foreach ($filters as $column => $value) {
          if ($value != '0') {
            $column = str_replace ( 'filter_', '', $column);
            $query->condition($column, $value);
          }
        }
      }

      // Set up the paginated query
      $count_query = clone $query;
      $count_query->addExpression('Count(*)');
      $paged_query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
      $paged_query->limit($items_per_page);
      $paged_query->fields('g');
      $paged_query->orderBy('import_date', 'DESC');
      $paged_query->setCountQuery($count_query);
      $rows = $paged_query->execute()->fetchAllAssoc('nid');
    }
    catch (Exception $e) {
      $msg = $e->getMessage();
      \Drupal::logger('gdocs_publish')->notice("DB exception: %m", array('%m' => $msg));
      drupal_set_message(t("Database exception: $msg."), 'error');
    }

    // Create list array, create item for each row of results, add item to list
    if (! empty($rows)) {
      $list = array();
      $content_type_labels = node_type_get_names();
      $E = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $E->loadMultiple(array_keys($rows));

      foreach (array_keys($rows) as $nid) {
        $item = array();
        $item['gdoc'] = new FormattableMarkup('<a href="https://docs.google.com/document/d/:gid" target="_blank">@title</a>', [
          ':gid' => $rows[$nid]->gd_id,
          '@title' => $rows[$nid]->gd_title
        ]);
        $item['import_date'] = format_date($rows[$nid]->import_date, 'short');
        $item['gd_user'] = $rows[$nid]->gd_user;
        $item['success'] = $rows[$nid]->success ? 'YES' : 'NO';
        $item['user'] = \Drupal\user\Entity\User::load($rows[$nid]->uid)->getUsername();

        if (isset($nodes[$nid])) {
          $item['node'] = new FormattableMarkup('<a href="/node/:nid">@title</a>', [
            ':nid' => $nid,
            '@title' => $nodes[$nid]->getTitle()
          ]);
          $item['published'] = $nodes[$nid]->isPublished() ? 'YES' : 'NO';
          $ctype = $nodes[$nid]->getType();
          $item['type'] = isset($content_type_labels[$ctype]) ? $content_type_labels[$ctype] : 'INVALID';
        }
        else {
          $item['node'] = 'DELETED';
          $item['published'] = 'N/A';
          $item['type'] = 'N/A';
        }

        $list[$nid] = $item;
      }
    }

    $header = [
      'node'        => t('Drupal Node'),
      'user'        => t('Drupal user'),
      'gdoc'        => t('Google Document'),
      'type'        => t('Content type'),
      'success'     => t('Imported'),
      'published'   => t('Published'),
      'gd_user'     => t('Google User'),
      'import_date' => t('Import Date'),
    ];

    return [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $list
    ];
  }
 }
