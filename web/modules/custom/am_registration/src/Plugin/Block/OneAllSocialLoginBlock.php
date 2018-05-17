<?php

namespace Drupal\am_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'Social Login' block.
 *
 * @Block(
 *   id = "social_login_block_form",
 *   admin_label = @Translation("Social Login"),
 *   category = @Translation("One All Social Login Block")
 * )
 */
class OneAllSocialLoginBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\social_login\Form\SocialLoginBlockForm');
    return $form;
   }
}
