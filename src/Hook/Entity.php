<?php

namespace Drupal\node_lock\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;

/**
 * Hook implementations for the Node Lock module.
 */
class Entity {

  public function __construct(
    private LockInterface $lock,
  ) {
  }

  /**
   * Implements hook_entity_delete().
   */
  #[Hook('entity_delete')]
  public function entityDelete(EntityInterface $entity): void {
    if ($entity instanceof NodeInterface) {
      $this->lock->deleteLock($entity);
    }
  }

}
