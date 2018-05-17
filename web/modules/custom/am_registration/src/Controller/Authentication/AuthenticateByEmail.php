<?php

namespace Drupal\am_registration\Controller\Authentication;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Password\PasswordInterface;

class AuthenticateByEmail extends ControllerBase{

/**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The password hashing service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordChecker;

  /**
   * Constructs a UserAuth object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Password\PasswordInterface $password_checker
   *   The password service.
   */
  public function __construct(EntityManagerInterface $entity_manager, PasswordInterface $password_checker) {
    $this->entityManager = $entity_manager;
    $this->passwordChecker = $password_checker;
  }

  /**
   * {@inheritdoc}
   */
public function authenticate_by_email($email, $password) {
  $uid = FALSE;

  if (!empty($email) && strlen($password) > 0) {
    $account_search = $this->entityManager->getStorage('user')->loadByProperties(array('mail' => $email));

    if ($account = reset($account_search)) {
      if ($this->passwordChecker->check($password, $account->getPassword())) {
        // Successful authentication.
        $uid = $account->id();

        // Update user to new password scheme if needed.
        if ($this->passwordChecker->needsRehash($account->getPassword())) {
          $account->setPassword($password);
          $account->save();
        }
      }
    }
  }

  return $uid;
}

}