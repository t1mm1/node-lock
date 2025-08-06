<?php

namespace Drupal\node_lock\Hook;

use Drupal\node\NodeInterface;
use Drupal\node_lock\Helper;
use Drupal\node_lock\Lock\LockInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Generic form alter hook implementation for the Content Lock module.
 */
class FormAlter {
  use DependencySerializationTrait;
  use StringTranslationTrait;

  public function __construct(
    private LockInterface $lock,
    private Helper $helper,
    private MessengerInterface $messenger,
    private ConfigFactoryInterface $configFactory,
    private AccountInterface $currentUser,
  ) {
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    if (!$form_state->getFormObject() instanceof EntityFormInterface) {
      return;
    }

    /** @var \Drupal\node\NodeInterface $entity */
    // If current entity is not
    $entity = $form_state->getFormObject()->getEntity();
    if (!$entity instanceof NodeInterface) {
      return;
    }

    // If this is a new created one.
    if ($entity->isNew()) {
      return;
    }

    // Check if we must lock this entity.
    if (!$this->lock->isLockable($entity)) {
      return;
    }

    // Check is it edit form and set visible for lock/unlock buttons.
    $is_edit_form = $this->helper->isEditForm($entity, $form_id);

    $is_locked_entity = $this->lock->isLockedEntity($entity);
    // If node was not locked yet.
    if (!$is_locked_entity) {
      // Add lock button.
      $form['actions']['lock'] = $this->helper->getButton($entity, FALSE, $is_edit_form);
    }

    // If entity has lock.
    if ($is_locked_entity) {
      // Set message for users.
      $is_owner = $this->lock->isOwner($entity);
      $lock = $this->lock->getLock($entity);

      if (!$is_owner) {
        $this->helper->setMessageAsUser($lock);
      }
      else {
        $this->helper->setMessageAsOwner($lock);
      }

      // Disable form.
      $this->helper->disableForm($form);

      // Unset actions.
      $this->helper->unsetActions($form);

      // Unset state.
      $this->helper->unsetModerationState($form);

      // Add button.
      $form['actions']['unlock'] = $this->helper->getButton($entity, TRUE, $is_edit_form, $is_owner, $this->lock->isBypass());
    }
  }

}
