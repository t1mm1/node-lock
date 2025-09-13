<?php

namespace Drupal\node_lock\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for enable node lock forms.
 */
class LockForm extends ConfirmFormBase {

  /**
   * The entity to lock.
   *
   * @var NodeInterface
   */
  protected $entity;

  /**
   * Lock service.
   *
   * @var LockInterface
   */
  protected LockInterface $lock;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    LockInterface $lock,
  ) {
    $this->lock = $lock;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('node_lock.lock'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_lock_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to lock node %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.node.edit_form', ['node' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Lock current page');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $this->entity = $node;
    $form = parent::buildForm($form, $form_state);
    $form['description']['#markup'] = $this->t('You are trying to lock page.<br />After locking, current page will be not available to edit by others users.');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->lock->setLock($this->entity);

      $message = $this->t('You have locked this page successfully.<br />Other authorised users will not be able to edit and publish content within the page.');
      $this->messenger()->addMessage(Markup::create($message));

      $form_state->setRedirect('entity.node.edit_form', ['node' => $this->entity->id()]);
    }
    catch (\Exception $exception) {
      $this->messenger()->addMessage('Error occured while the process, please contact the responsible project manager: ' . $exception->getMessage(), NULL, 'error');
    }
  }

}
