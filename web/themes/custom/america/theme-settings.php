<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Theme\ThemeSettings;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\Core\Form;

function america_form_system_theme_settings_alter(&$form, Drupal\Core\Form\FormStateInterface $form_state) {
  $form['america_settings']['appeals_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Appeals Mode'),
    '#default_value' => theme_get_setting('appeals_mode', 'america'),
  );
}