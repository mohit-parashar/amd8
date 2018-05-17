<?php
/**
 * @file
 * Contains Drupal\am_newsletter\Form\amNewsletter\amNewsletterUpdateForm.
 */
namespace Drupal\am_newsletter\Form\AMNewsletter;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\am_bsd_tools\Controller\amBSDToolsController;

/**
 * Class amNewsletterUpdateForm.
 *
 */
class AMNewsletterUpdateForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'am_newsletter_update_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    try{
          $form['#attached']['library'][] = 'am_newsletter/email_preference';
          $bsdClient = new amBSDToolsController();
          // Check email is set or not.
          if (isset($_GET['email']) && $_GET['email'] != '') {
              $string = strtolower($_GET['email']);
              $find    = array("~", "!", "(", ")", "^", "*");
              $replace = array("a","e","i","o","u","@");
              $email = str_replace($find, $replace, $string);
              $field_cons_id = $bsdClient->getConstituentsByEmail($email);
              // Check email address is exist in the bsd system or not.
              if($field_cons_id != '0' || $field_cons_id != 0) {
                  $groups = $bsdClient->listConstituentGroups();
                  $activeGroups = array();

                  $form['field_cons_id_0_value'] = array(
                    '#type' => 'hidden',
                    '#value' => $field_cons_id,
                    '#attributes' => array('id' => 'edit-field-cons-id-0-value'),
                    );

                  $form['email_preference'] = array(
                      '#type' => 'checkboxes',
                      '#options' => $groups,
                      '#title' => t('Manage Email Preferences'),
                      '#weight' => 3,
                    );

                  $form['unsubscribe_me'] = array(
                      '#type' => 'checkbox',
                      '#title' => t('Unsubscribe me from all emails'),
                      '#weight' => 3,
                    );

                  $form['update_preference'] = array(
                      '#type' => 'button',
                      '#value' => t('Update Preferences'),
                      '#weight' => 3,
                  );
              } else {
                  $form['email_preference'] = array(
                      '#markup' => '<div class="alert alert-dismissable fade in welcome-alert"><a href="" class="close" data-dismiss="alert" aria-label="close">×</a><span class="message">Invalid email address.</span></div>',
                  );
              }
          } else {

                  $form['email_preference'] = array(
                      '#markup' => '<div class="alert alert-dismissable fade in welcome-alert"><a href="" class="close" data-dismiss="alert" aria-label="close">×</a><span class="message">Invalid link.</span></div>',
                  );
          }

      } catch (Exception $e) {
          drupal_set_message($e."Some error occured","error");
      }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //parent::submitForm($form, $form_state);

  }
}
