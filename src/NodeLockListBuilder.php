<?php

namespace Drupal\node_lock;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NodeLockListBuilder extends EntityListBuilder {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    parent::__construct($entity_type, $storage);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type,
  ) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['parent'] = $this->t('Node');
    $header['uid'] = $this->t('User');
    $header['created'] = $this->t('Created');

    $parent = parent::buildHeader();
    $parent['operations'] = '';

    return $header + $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\node_lock\Entity\LockEntity $entity */
    $row['id'] = $entity->id();
    $row['parent'] = $this->getNodeLink($entity);
    $row['uid'] = $this->getProfileLink($entity);
    $row['created'] = $this->getLockCreated($entity, 'long');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no locks.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity): array {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      unset($operations['edit']);
    }

    if (isset($operations['delete'])) {
      $operations['delete']['title'] = $this->t('Unlock');
    }

    return $operations;
  }

  /**
   * Help function for getting parent Node link.
   *
   * @param EntityInterface $entity
   *   The item list entity.
   *
   * @return Link|null
   *   Link to node.
   */
  public function getNodeLink(EntityInterface $entity): Link|null {
    $parent = $entity->get('parent')->target_id;

    try {
      $node = $this->entityTypeManager->getStorage('node')->load($parent);
    }
    catch (\Exception $e) {
      return NULL;
    }

    if ($node) {
      $link = Link::fromTextAndUrl(
        $node->label(),
        $node->toUrl(),
      );
    }
    else {
      $link = $this->t('Node [@nid] was removed.', ['@nid' => $parent]);
    }

    return $link;
  }

  /**
   * Help function for getting lock owner profile link.
   *
   * @param EntityInterface $entity
   *   The item list entity.
   *
   * @return Link|null
   *   Link to profile.
   */
  public function getProfileLink(EntityInterface $entity): Link|null {
    $uid = $entity->get('uid')->target_id;

    try {
      $user = $this->entityTypeManager->getStorage('user')->load($uid);
    }
    catch (\Exception $e) {
      return NULL;
    }

    if ($user) {
      $link = $user->toLink($user->getDisplayName());
    }
    else {
      $link = $this->t('[User @uid was removed]', ['@uid' => $uid]);
    }

    return $link;
  }

  /**
   * Help function for getting unlock Node link.
   *
   * @param EntityInterface $entity
   *   The item list entity.
   * @param string $format
   *   The date format.
   *
   * @return string
   *   Link to unlock node.
   */
  public function getLockCreated(EntityInterface $entity, $format = 'short'): string {
    return \Drupal::service('date.formatter')->format($entity->get('created')->value, $format);
  }

}
