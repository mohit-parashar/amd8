<?php
/**
 * @file
 * Provides controller for blocks edition.
 *
 * Sponsored by: www.freelance-drupal.com
 */

namespace Drupal\feadmin_block\Controller;

use Drupal\block\BlockInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FeAdminBlockController extends ControllerBase {

  /**
   * The entity storage for views.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a FeAdminBlockController object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage for blocks.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(EntityStorageInterface $storage, LoggerInterface $logger) {
    $this->storage = $storage;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('block'),
      $container->get('logger.factory')->get('Front-End Administration')
    );
  }

  /**
   * Save blocks and regions after there re-arranging.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function sortBlocks(Request $request) {

    // Retrieve POST content.
    $content = $request->getContent();

    // In case of ping with no data: return HTTP 500.
    if (empty($content)) {
      $this->logger->warning('sortBlocks pinged with no data.');
      return new JsonResponse(null, 500);
    }
    // 2nd param to get as array
    $params = json_decode($content, TRUE);

    // Retrieve the blocks that needs an update.
    $entities = $this->storage->loadMultiple($params['blocks']);

    // Iterate through those blocks and save the change.
    $regionName = $params['region'];
    /** @var \Drupal\block\BlockInterface[] $entities */
    foreach ($entities as $entity_id => $entity) {
      $entity->setWeight(array_search($entity_id, $params['blocks']));
      $entity->setRegion($regionName);
      if ($entity->getRegion() == BlockInterface::BLOCK_REGION_NONE) {
        $entity->disable();
      }
      else {
        $entity->enable();
      }
      $entity->save();
    }

    // Return a positive feedback.
    return new JsonResponse('The block settings have been updated.');
  }

  /**
   * Delete block.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function deleteAction(Request $request) {

  }

}

?>