<?php
/**
 * @file
 * Contains \Drupal\am_digital_authorization\Form\DigitalAuthorizationForm.
 */

namespace Drupal\am_digital_authorization\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\am_digital_authorization\Controller\DigitalAuthorization;

/**
 * DigitalAuthorizationForm form.
 */
class DigitalAuthorizationForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_authorization_hash_form';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'digitedition.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $config = $this->config('digitedition.settings');

    $help_text = $config->get('dp_help_text');

    $form['publisher_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Publisher ID'),
      '#default_value' => $config->get('publisher_id'),
    );  
  
    $form['publication_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Publication ID'),
      '#default_value' => $config->get('publication_id'),
    );

    $form['dp_help_text'] = array(
      '#type' => 'text_format',
      '#format' => isset($help_text[format]) ? $help_text[format] : 'full_html',
      '#title' => $this->t('Help Text'),
      '#default_value' => isset($help_text['value']) ? $help_text['value'] : '',
    );

    return parent::buildForm($form, $form_state);
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
       // Retrieve the configuration
       $this->configFactory->getEditable('digitedition.settings')
      // Set the submitted configuration setting
      ->set('publisher_id', $form_state->getValue('publisher_id'))
      ->set('publication_id', $form_state->getValue('publication_id'))
      ->set('dp_help_text', $form_state->getValue('dp_help_text'))
      ->save();

    parent::submitForm($form, $form_state);
  }
  
}