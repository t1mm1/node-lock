<?php

namespace Drupal\node_lock\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;

/**
 * Interface of Access service.
 */
class Access implements AccessInterface {

  /**
   * Lock service configs.
   *
   * @var ImmutableConfig
   */
  protected ImmutableConfig $configs;

  /**
   * Lock service.
   *
   * @var LockInterface
   */
  protected LockInterface $lock;

  /**
   * @inheritDoc
   */
  public function __construct(
    LockInterface $lock,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->lock = $lock;
    $this->configs = $config_factory->get('node_lock.settings');
  }

  /**
   * @inheritDoc
   */
  public function checkConditions(array $conditions, $config): AccessResultInterface {
    foreach ($conditions as $condition) {
      if (!$condition) {
        return AccessResult::forbidden()->addCacheableDependency($config);
      }
    }
    return AccessResult::allowed()->addCacheableDependency($config);
  }

  /**
   * @inheritDoc
   */
  public function lock(NodeInterface $node): AccessResultInterface {
    return $this->checkConditions([
      $this->lock->isEnabled(),
      $this->lock->isLockable($node),
      !$this->lock->isLockedEntity($node),
    ], $this->configs);
  }

  /**
   * @inheritDoc
   */
  public function unlock(NodeInterface $node): AccessResultInterface {
    return $this->checkConditions([
      $this->lock->isEnabled(),
      $this->lock->isLockable($node),
      $this->lock->isLockedEntity($node),
      $this->lock->isOwner($node) || $this->lock->isBypass(),
    ], $this->configs);
  }

}
