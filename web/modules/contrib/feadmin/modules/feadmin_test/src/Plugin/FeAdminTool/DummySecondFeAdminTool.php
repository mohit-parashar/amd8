<?php

/**
 * @file
 * Contains \Drupal\feadmin_block\Plugin\FeAdminTool\BlockFeAdminTool.
 * 
 * Sponsored by: www.freelance-drupal.com
 */

namespace Drupal\feadmin_test\Plugin\FeAdminTool;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\feadmin\FeAdminTool\FeAdminToolBase;

/**
 * Provides a second dummy front-end administration tool.
 *
 * @FeAdminTool(
 *   id = "feadmin_dummy_second",
 *   label = @Translation("Dummy tool 2"),
 *   description = @Translation("Dummy 2 Front-End Administration tool, used for testing purpose.")
 * )
 */
class DummySecondFeAdminTool extends FeAdminToolBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {}

  /**
   * Form constructor.
   *
   * Plugin forms are embedded in other forms. In order to know where the plugin
   * form is located in the parent form, #parents and #array_parents must be
   * known, but these are not available during the initial build phase. In order
   * to have these properties available when building the plugin form's
   * elements, let this method return a form element that has a #process
   * callback and build the rest of the form in the callback. By the time the
   * callback is executed, the element's #parents and #array_parents properties
   * will have been set by the form API. For more documentation on #parents and
   * #array_parents, see \Drupal\Core\Render\Element\FormElement.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   *
   * @return array
   *   The form structure.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // TODO: Implement buildConfigurationForm() method.
  }

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateConfigurationForm() method.
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitConfigurationForm() method.
  }
}
