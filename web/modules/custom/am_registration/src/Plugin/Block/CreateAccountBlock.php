<?php
/**
 * @file
 * Contains \Drupal\am_registration\Plugin\Block\CreateAccountBlock.
 */
namespace Drupal\am_registration\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
/**
 * Provides a 'registration' block.
 *
 * @Block(
 *   id = "create_account_form",
 *   admin_label = @Translation("Create Account"),
 *   category = @Translation("User Registration")
 * )
 */
class CreateAccountBlock extends BlockBase {
  
  // /**
  //  * {@inheritdoc}
  //  */
  // public function access(AccountInterface $account) {
  //   return $account->hasPermission('access content');
  // }  

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\am_registration\Form\CreateAccountForm');
    return $form;
    // return array(
    //   '#type' => 'markup',
    //   '#markup' => 'This block list the article.',
    // );
   }
}