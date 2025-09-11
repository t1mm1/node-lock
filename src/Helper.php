<?php

namespace Drupal\node_lock;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Represents a Helper service.
 */
class Helper {

  use StringTranslationTrait;

  /**
   * The usage url service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   *   The date formatter service.
   */
  protected DateFormatter $dateFormatter;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * Constructs a new Helper object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The entity repository manager.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The date formatter service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user account.
   */
  public function __construct(
    MessengerInterface $messenger,
    DateFormatter $dateFormatter,
    AccountInterface $current_user,
  ) {
    $this->messenger = $messenger;
    $this->dateFormatter = $dateFormatter;
    $this->currentUser = $current_user;
  }

  /**
   * Help function for getting button lock/unlock.
   *
   * @param EntityInterface $entity
   *   The source entity.
   * @param bool $lock
   *   The check for lock.
   * @param bool $access
   *   The check for access.
   * @param bool $owner
   *   The check for owner.
   *
   * @return array
   *   The targets id array.
   */
  public function getButton(EntityInterface $entity, bool $lock, bool $access = FALSE, bool $owner = FALSE, $bypass = FALSE): array {
    if ($lock) {
      $label = $this->t('Unlock');
      $route = 'node_lock.form.unlock';
      $access  = $access && ($owner || $bypass);
    }
    else {
      $label = $this->t('Lock');
      $route = 'node_lock.form.lock';
    }

    return [
      '#type' => 'link',
      '#title' => $label,
      '#attributes' => [
        'class' => ['button'],
      ],
      '#url' => Url::fromRoute($route, ['node' => $entity->id()]),
      '#weight' => 99,
      '#access' => $access,
    ];
  }

  /**
   * Help function for checking is current for edit ot not.
   *
   * @param EntityInterface $entity
   *   The source entity.
   * @param string $form_id
   *   The form id.
   *
   * @return bool
   *   The check for edit form.
   */
  public function isFormEdit(EntityInterface $entity, string $form_id): bool {
    if ($form_id !== $entity->getEntityTypeId() . '_' . $entity->bundle() . '_edit_form') {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Help function for checking is current for delete ot not.
   *
   * @param EntityInterface $entity
   *   The source entity.
   * @param string $form_id
   *   The form id.
   *
   * @return bool
   *   The check for edit form.
   */
  public function isFormDelete(EntityInterface $entity, string $form_id): bool {
    if ($form_id !== $entity->getEntityTypeId() . '_' . $entity->bundle() . '_delete_form') {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Help function for unset actions elements.
   *
   * @param array $form
   *   The source form.
   * @param bool $owner
   *   The owner of lock.
   */
  public function unsetActions(array &$form, bool $owner = FALSE): void {
    $actions = ['publish', 'unpublish'];
    if (!$owner) {
      $actions = $actions + ['delete'];
    }
    foreach ($actions as $action) {
      if (isset($form['actions'][$action])) {
        unset($form['actions'][$action]);
      }
      if (isset($form[$action])) {
        unset($form[$action]);
      }
    }
  }

  /**
   * Help function for disable form.
   *
   * @param array $form
   *   The source form.
   */
  public function disableForm(array &$form): void {
    $form['#disabled'] = TRUE;
  }

  /**
   * Help function for unset moderation state element.
   *
   * @param array $form
   *   The source form.
   */
  public function unsetModerationState(array &$form): void {
    if (isset($form['moderation_state'])) {
      unset($form['moderation_state']);
    }
  }

  /**
   * Help function for set message for owner of lock.
   *
   * @param EntityInterface $entity
   *   The lock entity.
   */
  public function setMessageAsUser(EntityInterface $entity): void {
    $this->messenger->addMessage(
      $this->t('This node was locked by <strong>@link</strong> at <strong>@date</strong>.', [
        '@link' => $this->getOwnerOrUserName($entity),
        '@date' => $this->getDateCreated($entity),
      ])
    );
  }

  /**
   * Help function for set message for user (not owner) of lock.
   *
   * @param EntityInterface $entity
   *   The lock entity.
   */
  public function setMessageAsOwner(EntityInterface $entity): void {
    $this->messenger->addMessage(
      $this->t('This node was locked by <strong>you</strong> at <strong>@date</strong>.', [
        '@date' => $this->getDateCreated($entity),
      ])
    );
  }

  /**
   * Help function for getting format date of lock created.
   *
   * @param EntityInterface $entity
   *   The lock entity.
   * @return string
   *   The date in custom format.
   */
  public function getDateCreated(EntityInterface $entity): string {
    return $this->dateFormatter->format(
      $entity->getCreatedTime(),
      'custom',
      'd.m.Y H:i'
    );
  }

  /**
   * Help function for getting lock owner username.
   *
   * @param EntityInterface $entity
   *   The lock entity.
   * @return mixed
   *   The username, or link to profile.
   */
  public function getOwnerOrUserName(EntityInterface $entity): mixed {
    $user = $entity->getUser();

    if (empty($user)) {
      return t('[deleted user]');
    }

    if ($user->access('view', $this->currentUser, TRUE)->isAllowed()) {
      return $user->toLink($user->getDisplayName())->toString();
    }

    return $user->getDisplayName();
  }

}
