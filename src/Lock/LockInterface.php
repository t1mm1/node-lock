<?php

namespace Drupal\node_lock\Lock;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node_lock\Entity\LockEntity;

/**
 * Interface of Lock service.
 */
interface LockInterface {

  /**
   * Get locked entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that has lock or not.
   *
   * @return EntityInterface|false
   *   The lock entity or false.
   */
  public function getLock(EntityInterface $entity): EntityInterface|false;

  /**
   * Get locked entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to lock.
   *
   * @return bool
   *   The user is owner or not.
   */
  public function isOwner(EntityInterface $entity): bool;

  /**
   * Has bypass permissions.
   *
   * @return bool
   *   The user is owner or not.
   */
  public function isBypass(): bool;

  /**
   * Set lock for entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return EntityInterface|false
   *   Result of lock entity create.
   */
  public function setLock(EntityInterface $entity): EntityInterface|false;

  /**
   * Delete lock entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   Result of lock entity delete.
   */
  public function deleteLock(EntityInterface $entity): bool;

  /**
   * Check whether a node is configured to be locked.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE is entity is lockable
   */
  public function isLockable(EntityInterface $entity): bool;

  /**
   * Check lock status.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   Return TRUE OR FALSE.
   */
  public function isLockedEntity(EntityInterface $entity): bool;

  /**
   * Check lock status by user.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param int $uid
   *   The user id.
   *
   * @return bool
   *   Return TRUE OR FALSE.
   */
  public function isLockedUser(EntityInterface $entity, int $uid): bool;

  /**
   * Check if lock service is enabled.
   *
   * @return bool
   *   Lock service enabled or not.
   */
  public function isEnabled(): bool;

}
