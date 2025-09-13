<?php

namespace Drupal\node_lock\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;

/**
 * Interface of Access service.
 */
class Access implements AccessInterface {

  /**
   * Lock service.
   *
   * @var LockInterface
   */
  protected LockInterface $lock;

  /**
   * @inheritDoc
   */
  public function __construct(LockInterface $lock) {
    $this->lock = $lock;
  }

  /**
   * @inheritDoc
   */
  public function lock(NodeInterface $node): AccessResultInterface {
    // If service not enabled.
    if (!$this->lock->isEnabled()) {
      return AccessResult::forbidden();
    }

    // If entity is not lockable.
    if (!$this->lock->isLockable($node)) {
      return AccessResult::forbidden();
    }

    // If entity already locked.
    if ($this->lock->isLockedEntity($node)) {
      return AccessResult::forbidden();
    }

    $access = AccessResult::allowed();
    $access->addCacheableDependency(NULL);

    return $access;
  }

  /**
   * @inheritDoc
   */
  public function unlock(NodeInterface $node): AccessResultInterface {
    // If service not enabled.
    if (!$this->lock->isEnabled()) {
      return AccessResult::forbidden();
    }

    // If entity is not lockable.
    if (!$this->lock->isLockable($node)) {
      return AccessResult::forbidden();
    }

    // If entity is not locked.
    if (!$this->lock->isLockedEntity($node)) {
      return AccessResult::forbidden();
    }

    // If user not owner of lock or has no permissions for unlock bypass.
    if (!$this->lock->isOwner($node) && !$this->lock->isBypass()) {
      return AccessResult::forbidden();
    }

    $access = AccessResult::allowed();
    $access->addCacheableDependency(NULL);

    return $access;
  }

}
