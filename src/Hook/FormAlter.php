<?php

namespace Drupal\node_lock\Hook;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\node_lock\NodeLockHelper;
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
    private NodeLockHelper $nodeLockHelper,
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

    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->getFormObject()->getEntity();
    if (!$node instanceof NodeInterface) {
      return;
    }

    // If this is a new created one.
    if ($node->isNew()) {
      return;
    }

    // Check if we must lock this entity.
    if (!$this->lock->isLockable($node)) {
      return;
    }

    // Check is it edit form and set visible for lock/unlock buttons.
    $is_edit_form = $this->nodeLockHelper->isFormEdit($node, $form_id);

    $is_locked_entity = $this->lock->isLockedEntity($node);
    // If node was not locked yet.
    if (!$is_locked_entity) {
      $form['actions']['lock'] = $this->nodeLockHelper->getButton($node, FALSE, $is_edit_form);
    }

    // If entity has lock.
    if ($is_locked_entity) {
      // Set message for users.
      $is_owner = $this->lock->isOwner($node);
      $lock = $this->lock->getLock($node);

      if (isset($form['advanced'])) {
        $description = $is_owner ? $this->nodeLockHelper->getMessageAsOwner($lock) : $this->nodeLockHelper->getMessageAsUser($lock);
        if ($this->currentUser->hasPermission('administer site configuration')) {
          $description .= '<br /><br />' . $this->t('To change the default settings go to @settings_link.', [
            '@settings_link' => Link::fromTextAndUrl(t('settings page'), Url::fromRoute('node_lock.settings', [], [
              'attributes' => [
                'target' => '_blank',
              ],
            ]))->toString(),
          ]);
        }

        $form['node_lock_options'] = [
          '#type' => 'details',
          '#title' => $this->t('Lock node settings'),
          '#description' => $description,
          '#group' => 'advanced',
          '#weight' => 20,
          '#attributes' => [
            'class' => ['node-form-lock-options'],
          ],
          '#open' => 1,
        ];
      }

      if (!$is_owner) {
        $this->nodeLockHelper->setMessageAsUser($lock);
      }
      else {
        $this->nodeLockHelper->setMessageAsOwner($lock);
      }

      if ($this->nodeLockHelper->isFormDelete($node, $form_id) && $is_owner) {
        return;
      }

      // Disable form.
      $this->nodeLockHelper->disableForm($form);

      // Unset actions.
      $this->nodeLockHelper->unsetActions($form, $is_owner);

      // Unset state.
      $this->nodeLockHelper->unsetModerationState($form);

      // Add button.
      $form['actions']['unlock'] = $this->nodeLockHelper->getButton($node, TRUE, $is_edit_form, $is_owner, $this->lock->isBypass());
    }
  }

}
