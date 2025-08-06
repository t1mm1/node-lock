<?php

namespace Drupal\node_lock\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node_lock\Lock\LockInterface;

/**
 * Hook implementations for the Node Lock module.
 */
class Lock {

  use StringTranslationTrait;

  public function __construct(
    private LockInterface $lock,
    private MessengerInterface $messenger,
    private ConfigFactoryInterface $configFactory,
    private AccountInterface $currentUser,
    private TimeInterface $time,
    private Connection $database,
    private EntityTypeManagerInterface $entityTypeManager,
    private DateFormatterInterface $dateFormatter,
    private LoggerChannelFactoryInterface $logger,
  ) {
  }

  /**
   * Implements hook_user_predelete().
   *
   * Delete content locks entries when a user gets deleted. If a user has
   * permission to cancel or delete a user then it is not necessary to check
   * whether they can break locks.
   */
//  #[Hook('user_predelete', order: Order::First)]
//  public function userPredelete(UserInterface $account): void {
//    $this->contentLock->releaseAllUserLocks((int) $account->id());
//  }
//
//  /**
//   * Implements hook_user_cancel().
//   */
//  #[Hook('user_cancel')]
//  public function userCancel($edit, UserInterface $account, $method): void {
//    $this->contentLock->releaseAllUserLocks((int) $account->id());
//  }

  /**
   * Implements hook_ENTITY_TYPE_presave() for views.
   *
   * When a view is saved, prevent using a cache if the content_lock data is
   * displayed.
   */
//  #[Hook('view_presave')]
//  public function viewPresave(ViewEntityInterface $view): void {
//    $viewDependencies = $view->getDependencies();
//    if (in_array('content_lock', $viewDependencies['module'] ?? [], TRUE)) {
//      $changed_cache = FALSE;
//      $displays = $view->get('display');
//      foreach ($displays as &$display) {
//        if (isset($display['display_options']['cache']['type']) && $display['display_options']['cache']['type'] !== 'none') {
//          $display['display_options']['cache']['type'] = 'none';
//          $changed_cache = TRUE;
//        }
//      }
//      if ($changed_cache) {
//        $view->set('display', $displays);
//        $warning = $this->t('The selected caching mechanism does not work with views including content lock information. The selected caching mechanism was changed to none accordingly for the view %view.', ['%view' => $view->label()]);
//        $this->messenger->addWarning($warning);
//      }
//    }
//  }

  /**
   * Implements hook_content_lock_entity_lockable().
   */
//  #[Hook('content_lock_entity_lockable', module: 'trash')]
//  public function trashContentEntityLockable(EntityInterface $entity, array $config, ?string $form_op = NULL): bool {
//    return !trash_entity_is_deleted($entity);
//  }

  /**
   * Implements hook_entity_operation().
   */
//  #[Hook('entity_operation')]
//  public function entityOperation(EntityInterface $entity): array {
//    $operations = [];
//
//    if ($this->contentLock->isLockable($entity)) {
//      $lock = $this->contentLock->fetchLock($entity);
//
//      if ($lock && $this->currentUser->hasPermission('break content lock')) {
//        $entity_type = $entity->getEntityTypeId();
//        $route_parameters = [
//          'entity' => $entity->id(),
//          'langcode' => $this->contentLock->isTranslationLockEnabled($entity_type) ? $entity->language()
//            ->getId() : LanguageInterface::LANGCODE_NOT_SPECIFIED,
//          'form_op' => '*',
//        ];
//        $url = 'content_lock.break_lock.' . $entity->getEntityTypeId();
//        $operations['break_lock'] = [
//          'title' => $this->t('Break lock'),
//          'url' => Url::fromRoute($url, $route_parameters),
//          'weight' => 50,
//        ];
//      }
//    }
//
//    return $operations;
//  }

  /**
   * Implements hook_entity_delete().
   *
   * Releases locks when an entity is deleted. Note that users are prevented
   * from deleting locked content by content_lock_entity_access() if they do not
   * have the break lock permission.
   */
  #[Hook('entity_delete')]
  public function entityDelete(EntityInterface $entity): void {
    $t = 1;
    $t = 2;
//    if (!$this->contentLock->isLockable($entity)) {
//      return;
//    }
//
//    $data = $this->contentLock->fetchLock($entity, include_stale_locks: TRUE);
//    if ($data !== FALSE) {
//      $this->contentLock->release($entity);
//    }
  }

  /**
   * Implements hook_entity_access().
   */
//  #[Hook('entity_access')]
//  public function entityAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
//    $result = AccessResult::neutral();
//    if ($operation === 'delete') {
//      // Check if we must lock this entity.
//      $result->addCacheableDependency($this->configFactory->get('content_lock.settings'));
//      if ($this->contentLock->hasLockEnabled($entity->getEntityTypeId())) {
//        // The result is dependent on user IDs.
//        $result->cachePerUser();
//        // If the entity type is lockable this access result cannot be cached as
//        // you can lock an entity just by visiting the edit form.
//        $result->setCacheMaxAge(0);
//        $data = $this->contentLock->fetchLock($entity);
//        if ($data !== FALSE && $account->id() !== $data->uid) {
//          // If the entity is locked, and current user is not the lock's owner.
//          if ($account->id() !== $data->uid && !$account->hasPermission('break content lock')) {
//            $result = $result->andIf(AccessResult::forbidden('The entity is locked'));
//          }
//        }
//      }
//    }
//
//    return $result;
//  }

}
