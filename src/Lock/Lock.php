<?php

namespace Drupal\node_lock\Lock;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node_lock\Entity\LockEntity;

/**
 * Class Lock.
 *
 * The content lock service.
 */
class Lock implements LockInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The account service.
   *
   * @var AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The config service.
   *
   * @var ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs Lock service.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param AccountProxyInterface $current_user
   *   The current_user service.
   * @param ConfigFactoryInterface $config_factory
   *   The config service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * @inheritDoc
   */
  public function getLock(EntityInterface $entity): EntityInterface|false {
    try {
      $lock = $this->entityTypeManager->getStorage('node_lock')->loadByProperties([
        'parent' => $entity->id(),
      ]);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    if (empty($lock)) {
      return FALSE;
    }

    return reset($lock);
  }

  /**
   * @inheritDoc
   */
  public function isOwner(EntityInterface $entity): bool {
    /** @var $lock LockEntity */
    $lock = $this->getLock($entity);
    if (empty($lock)) {
      return FALSE;
    }

    $user = $lock->getUser();
    if (empty($user)) {
      return FALSE;
    }

    if ($user->id() !== $this->currentUser->id()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isBypass(): bool {
    return $this->currentUser->hasPermission('node lock bypass unlock');
  }

  /**
   * @inheritDoc
   */
  public function setLock(EntityInterface $entity): EntityInterface|false {
    try {
      $lock = LockEntity::create([
        'parent' => $entity->id(),
        'langcode' => $entity->language()->getId(),
      ]);
      $lock->save();

      // Clear cache for parent.
      Cache::invalidateTags(['node:' . $entity->id()]);

      return $lock;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * @inheritDoc
   */
  public function deleteLock(EntityInterface $entity): bool {
    $lock = $this->getLock($entity);
    if (empty($lock)) {
      return FALSE;
    }
    $lock->delete();

    // Clear cache for parent.
    Cache::invalidateTags(['node:' . $entity->id()]);

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isLockable(EntityInterface $entity): bool {
    $bundles = $this->configFactory->get('node_lock.settings')->get('bundles');
    if (!$bundles[$entity->bundle()]['enabled']) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isLockedEntity(EntityInterface $entity): bool {
    $lock = $this->getLock($entity);
    if (empty($lock)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isLockedUser(EntityInterface $entity, int $uid): bool {
    $lock = $this->getLock($entity);
    if ($lock && $lock->getUser()->id() !== $uid) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isEnabled(): bool {
    return $this->configFactory->get('node_lock.settings')->get('enabled');
  }

}
